<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use function Livewire\Volt\{state};
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

new class extends Component {
    use WithPagination;

    #[State]
    public $showFilters = false;
    
    #[State]
    public $showDeleteModal = false;
    
    #[State]
    public $showFormModal = false;
    
    #[State]
    public $modalMode = 'create'; // create, edit, view
    
    #[State]
    public $search = '';
    
    #[State]
    public $filters = [
        'role' => '',
        'status' => '',
    ];
    
    #[State]
    public $selectedUser = null;
    
    #[State]
    public $sortField = 'created_at';
    
    #[State]
    public $sortDirection = 'desc';
    
    #[State]
    public $isLoading = false;
    
    #[State]
    public $form = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'roles' => [],
        'is_active' => true,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => ['role' => '', 'status' => '']]
    ];

    public function mount() {
        abort_unless(auth()->user()->can('manage_users'), 403);
    }

    #[Computed]
    public function users()
    {
        $this->isLoading = true;
        
        try {
            $query = User::with('roles')
                ->when($this->search, function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->when($this->filters['role'], function($query) {
                    $query->whereHas('roles', function($q) {
                        $q->where('name', $this->filters['role']);
                    });
                })
                ->when($this->filters['status'], function($query) {
                    if ($this->filters['status'] === 'active') {
                        $query->whereNotNull('email_verified_at');
                    } elseif ($this->filters['status'] === 'inactive') {
                        $query->whereNull('email_verified_at');
                    }
                });
            
            // Handle the sorting
            $query->orderBy($this->sortField, $this->sortDirection);
            
            return $query->paginate(10);
        } finally {
            $this->isLoading = false;
        }
    }

    #[Computed]
    public function roles()
    {
        return Role::orderBy('name')->get();
    }

    #[Computed]
    public function stats()
    {
        return [
            'total' => User::count(),
            'active' => User::whereNotNull('email_verified_at')->count(),
            'admins' => User::role('admin')->count(),
        ];
    }

    public function rules() {
        $passwordRules = $this->modalMode === 'create' 
            ? ['required', Password::defaults(), 'confirmed']
            : ['nullable', Password::defaults(), 'confirmed'];
            
        return [
            'form.name' => 'required|string|max:255',
            'form.email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                Rule::unique('users', 'email')->ignore($this->selectedUser?->id)
            ],
            'form.password' => $passwordRules,
            'form.roles' => 'array',
            'form.is_active' => 'boolean',
        ];
    }

    public function create(): void {
        abort_unless(auth()->user()->can('create_user'), 403);
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showFormModal = true;
    }

    public function edit($id): void {
        abort_unless(auth()->user()->can('edit_user'), 403);
        
        $this->selectedUser = User::findOrFail($id);
        $this->form = [
            'name' => $this->selectedUser->name,
            'email' => $this->selectedUser->email,
            'password' => '',
            'password_confirmation' => '',
            'roles' => $this->selectedUser->roles->pluck('name')->toArray(),
            'is_active' => !is_null($this->selectedUser->email_verified_at),
        ];
        
        $this->modalMode = 'edit';
        $this->showFormModal = true;
    }

    public function view($id): void {
        $this->selectedUser = User::with('roles')->findOrFail($id);
        $this->form = [
            'name' => $this->selectedUser->name,
            'email' => $this->selectedUser->email,
            'password' => '',
            'password_confirmation' => '',
            'roles' => $this->selectedUser->roles->pluck('name')->toArray(),
            'is_active' => !is_null($this->selectedUser->email_verified_at),
        ];
        
        $this->modalMode = 'view';
        $this->showFormModal = true;
    }

    public function save(): void {
        if ($this->modalMode === 'view') return;
        
        $this->validate();

        if ($this->modalMode === 'create') {
            abort_unless(auth()->user()->can('create_user'), 403);
            
            $user = User::create([
                'name' => $this->form['name'],
                'email' => $this->form['email'],
                'password' => Hash::make($this->form['password']),
                'email_verified_at' => $this->form['is_active'] ? now() : null,
            ]);

            if (!empty($this->form['roles'])) {
                $user->assignRole($this->form['roles']);
            }

            $this->dispatch('notify', type: 'success', message: 'User created successfully.');
        } else {
            abort_unless(auth()->user()->can('edit_user'), 403);
            
            $data = [
                'name' => $this->form['name'],
                'email' => $this->form['email'],
                'email_verified_at' => $this->form['is_active'] ? 
                    ($this->selectedUser->email_verified_at ?? now()) : 
                    null,
            ];

            // Only update password if provided
            if (!empty($this->form['password'])) {
                $data['password'] = Hash::make($this->form['password']);
            }

            $this->selectedUser->update($data);

            // Sync roles
            $this->selectedUser->syncRoles($this->form['roles']);

            $this->dispatch('notify', type: 'success', message: 'User updated successfully.');
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id): void {
        abort_unless(auth()->user()->can('delete_user'), 403);
        $user = User::findOrFail($id);
        
        $this->selectedUser = $user;
        $this->showDeleteModal = true;
    }

    public function delete(): void {
        abort_unless(auth()->user()->can('delete_user'), 403);
        
        // Don't allow deleting yourself
        if ($this->selectedUser->id === auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'You cannot delete your own account.');
            $this->showDeleteModal = false;
            $this->selectedUser = null;
            return;
        }
        
        $this->selectedUser->delete();
        $this->showDeleteModal = false;
        $this->selectedUser = null;
        $this->dispatch('notify', type: 'success', message: 'User deleted successfully.');
    }
    
    public function sort($field): void {
        if ($this->sortField === $field) {
            // Toggle direction if clicking on the same field
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new field and default to ascending
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleFilters(): void {
        $this->showFilters = !$this->showFilters;
    }

    public function resetForm(): void {
        $this->form = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'roles' => [],
            'is_active' => true,
        ];
        $this->selectedUser = null;
    }

    public function resetFilters(): void {
        $this->reset('filters');
    }
}; ?>

<div class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl shadow-xl overflow-hidden relative">
    <!-- Header Section with Gradient -->
    <div class="h-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>
    
    <div class="p-8 border-b border-gray-200 dark:border-gray-700">
        <!-- Animated Header -->
        <div class="sm:flex sm:items-center sm:justify-between" 
             x-data="{}"
             x-intersect="$el.classList.add('animate-fade-in')">
            <div class="space-y-2">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <flux:icon name="users" class="w-8 h-8 text-[#02c9c2]" />
                    User Management
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage users, assign roles and permissions
                </p>
            </div>
            
            @can('create_user')
                <button 
                    wire:click="create"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                    wire:loading.attr="disabled"
                >
                    <flux:icon wire:loading.remove name="user-plus" class="w-5 h-5 mr-2" />
                    <flux:icon wire:loading name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                    New User
                </button>
            @endcan
        </div>

        <!-- Enhanced Search and Filters with Animation -->
        <div class="mt-8 space-y-4" 
             x-data="{}"
             x-intersect="$el.classList.add('animate-fade-in')">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1 relative">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                            <flux:icon name="magnifying-glass"
                                class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="search"
                            placeholder="Search users by name or email..."
                            class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            aria-label="Search users"
                        >
                    </div>
                </div>

                <!-- Filter Toggle Button -->
                <button
                    wire:click="toggleFilters"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-sm backdrop-blur-xl"
                >
                    <flux:icon name="funnel" class="w-5 h-5 mr-2" />
                    Filters
                    <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full px-2 py-0.5">
                        {{ array_filter($filters) ? count(array_filter($filters)) : '0' }}
                    </span>
                </button>
            </div>

            <!-- Filters Panel -->
            <div x-show="$wire.showFilters"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl shadow-sm space-y-4"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Role Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="user-group" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.role"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Roles</option>
                                @foreach($this->roles as $role)
                                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <flux:icon name="chevron-down" class="h-5 w-5 text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.status"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <flux:icon name="chevron-down" class="h-5 w-5 text-gray-400" />
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Actions -->
                <div class="flex flex-col md:flex-row items-center justify-center gap-4 col-span-2 mt-2">
                    <!-- Reset Filters Button -->
                    <button wire:click="resetFilters"
                        class="group relative overflow-hidden rounded-lg bg-gradient-to-r from-[#02c9c2] to-[#02a8a2] px-5 py-2.5 text-sm font-medium text-white shadow-md hover:shadow-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                        <!-- Background animation on hover -->
                        <span
                            class="absolute inset-0 translate-y-full bg-gradient-to-r from-[#012e2b] to-[#014e4a] group-hover:translate-y-0 transition-transform duration-300 ease-out"></span>
                        <!-- Content remains visible -->
                        <span class="relative flex items-center gap-2">
                            <flux:icon name="arrow-path"
                                class="h-4 w-4 transition-transform group-hover:rotate-180 duration-500" />
                            <span>Clear All Filters</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats cards -->
    <div class="px-8 py-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Total Users Card -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $this->stats['total'] }}</p>
                </div>
                <div class="h-12 w-12 rounded-lg flex items-center justify-center bg-blue-100 dark:bg-blue-900/30">
                    <flux:icon name="users" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>

            <!-- Active Users Card -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Users</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $this->stats['active'] }}</p>
                </div>
                <div class="h-12 w-12 rounded-lg flex items-center justify-center bg-green-100 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
            </div>

            <!-- Admin Users Card -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Admin Users</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $this->stats['admins'] }}</p>
                </div>
                <div class="h-12 w-12 rounded-lg flex items-center justify-center bg-purple-100 dark:bg-purple-900/30">
                    <flux:icon name="shield-check" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="p-8">
        <div class="relative overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl">
            <!-- Loading Overlay -->
            <div wire:loading.delay class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="flex items-center space-x-4">
                    <flux:icon name="arrow-path" class="w-8 h-8 text-[#02c9c2] animate-spin" />
                    <span class="text-gray-600 dark:text-gray-300 font-medium">Loading users...</span>
                </div>
            </div>

            <!-- Table -->
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-300 text-sm">
                    <tr>
                        <th wire:click="sort('name')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Name</span>
                                @if($sortField === 'name')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('email')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Email</span>
                                @if($sortField === 'email')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-4 font-medium">Roles</th>
                        <th wire:click="sort('email_verified_at')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Status</span>
                                @if($sortField === 'email_verified_at')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('created_at')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Created</span>
                                @if($sortField === 'created_at')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->users as $user)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-medium">
                                        {{ $user->initials() }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $user->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ match($role->name) {
                                                'admin' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300',
                                                'property_manager' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                                'content_editor' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300'
                                            } }}">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-500 dark:text-gray-400">No roles assigned</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                        <flux:icon name="check-circle" class="w-4 h-4 mr-1" />
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                        <flux:icon name="clock" class="w-4 h-4 mr-1" />
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-3">
                                    <button 
                                        wire:click="view({{ $user->id }})"
                                        class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-indigo-500 dark:bg-indigo-700/50 rounded-lg p-2"
                                        title="View User"
                                    >
                                        <flux:icon wire:loading.remove wire:target="view({{ $user->id }})" name="eye" class="w-5 h-5" />
                                        <flux:icon wire:loading wire:target="view({{ $user->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                    </button>
                                    
                                    @can('edit_user')
                                        <button 
                                            wire:click="edit({{ $user->id }})"
                                            class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-green-500 dark:bg-green-700/50 rounded-lg p-2"
                                            title="Edit User"
                                        >
                                            <flux:icon wire:loading.remove wire:target="edit({{ $user->id }})" name="pencil-square" class="w-5 h-5" />
                                            <flux:icon wire:loading wire:target="edit({{ $user->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                        </button>
                                    @endcan
                                    
                                    @can('delete_user')
                                        @unless($user->id === auth()->id())
                                            <button 
                                                wire:click="confirmDelete({{ $user->id }})"
                                                class="text-gray-200 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-500 transition-colors duration-150 bg-red-500 dark:bg-red-700/50 rounded-lg p-2"
                                                title="Delete User"
                                            >
                                                <flux:icon wire:loading.remove wire:target="confirmDelete({{ $user->id }})" name="trash" class="w-5 h-5" />
                                                <flux:icon wire:loading wire:target="confirmDelete({{ $user->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                            </button>
                                        @endunless
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <div class="text-center">
                                    <flux:icon name="users" class="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No users found</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $search ? 'Try adjusting your search or filter criteria.' : 'Get started by creating a new user.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($this->users->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $this->users->links('components.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <!-- User Form Modal -->
    <flux:modal wire:model="showFormModal" class="w-full max-w-4xl !p-0" @close="$wire.resetForm()">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            <div
                @class([
                    'bg-gradient-to-r px-6 py-4 border-b border-gray-200 dark:border-gray-700',
                    'from-[#02c9c2]/20 to-[#012e2b]/20 dark:from-[#02c9c2]/30 dark:to-[#012e2b]/30' => $modalMode !== 'view',
                    'from-purple-500/20 to-purple-600/20 dark:from-purple-900/30 dark:to-purple-700/30' => $modalMode === 'view' && $selectedUser && $selectedUser->hasRole('admin'),
                    'from-blue-500/20 to-blue-600/20 dark:from-blue-900/30 dark:to-blue-700/30' => $modalMode === 'view' && $selectedUser && $selectedUser->hasRole('property_manager'),
                    'from-green-500/20 to-green-600/20 dark:from-green-900/30 dark:to-green-700/30' => $modalMode === 'view' && $selectedUser && $selectedUser->hasRole('content_editor'),
                ])
            >
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="{{ $modalMode === 'create' ? 'user-plus' : ($modalMode === 'edit' ? 'user' : 'user-circle') }}"
                            class="w-5 h-5 text-[#02c9c2]" />
                        {{ $modalMode === 'create' ? 'Add New User' : ($modalMode === 'edit' ? 'Edit User' : 'View User Details') }}
                    </h3>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="user" class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                type="text"
                                wire:model="form.name"
                                @disabled($modalMode === 'view')
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                placeholder="Enter user's name"
                            >
                        </div>
                        @error('form.name') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="envelope" class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                type="email"
                                wire:model="form.email"
                                @disabled($modalMode === 'view')
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                placeholder="user@example.com"
                            >
                        </div>
                        @error('form.email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($modalMode !== 'view')
                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Password {{ $modalMode === 'edit' ? '(leave blank to keep current)' : '' }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="key" class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="password"
                                    wire:model="form.password"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                    placeholder="{{ $modalMode === 'edit' ? 'Enter new password' : 'Enter password' }}"
                                >
                            </div>
                            @error('form.password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="key" class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="password"
                                    wire:model="form.password_confirmation"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                    placeholder="Confirm password"
                                >
                            </div>
                        </div>
                    @endif

                    <!-- Active Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model="form.is_active"
                                @disabled($modalMode === 'view')
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <flux:icon name="chevron-down" class="h-5 w-5 text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Roles</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-2">
                            @foreach($this->roles as $role)
                                <div class="relative flex items-center">
                                    <div class="flex items-center h-5">
                                        <input
                                            id="role-{{ $role->id }}"
                                            wire:model="form.roles"
                                            type="checkbox"
                                            value="{{ $role->name }}"
                                            @disabled($modalMode === 'view')
                                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-[#02c9c2] focus:ring-[#02c9c2]"
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="role-{{ $role->id }}" class="font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($role->name) }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('form.roles')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($modalMode === 'view' && $selectedUser)
                        <!-- Additional user details for view mode -->
                        <div class="md:col-span-2 space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Account Details</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Created</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $selectedUser->created_at->format('F j, Y') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Last Updated</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $selectedUser->updated_at->format('F j, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Status</h4>
                                <div class="flex items-center mt-1">
                                    @if($form['is_active'])
                                        <flux:icon name="check-circle" class="h-5 w-5 text-green-500 mr-2" />
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            Email Verified: {{ $selectedUser->email_verified_at ? $selectedUser->email_verified_at->format('F j, Y') : 'Not verified' }}
                                        </span>
                                    @else
                                        <flux:icon name="x-circle" class="h-5 w-5 text-red-500 mr-2" />
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">Account Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="$set('showFormModal', false)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        {{ $modalMode === 'view' ? 'Close' : 'Cancel' }}
                    </button>
                    
                    @if($modalMode !== 'view')
                        <button type="button" wire:click="save"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white rounded-lg text-sm font-medium hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] shadow-sm"
                                wire:loading.attr="disabled">
                            <flux:icon wire:loading wire:target="save" name="arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                            {{ $modalMode === 'create' ? 'Create User' : 'Update User' }}
                        </button>
                    @else
                        @can('update', $selectedUser)
                            <button type="button" wire:click="edit({{ $selectedUser->id }})"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white rounded-lg text-sm font-medium hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] shadow-sm">
                                <flux:icon name="pencil-square" class="w-4 h-4 mr-2" />
                                Edit User
                            </button>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteModal" max-width="md" class="!p-0">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            <div class="bg-gradient-to-r from-red-500/20 to-red-600/20 dark:from-red-900/30 dark:to-red-700/30 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-circle" class="w-6 h-6 text-red-600" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Confirm Deletion
                    </h3>
                </div>
            </div>

            <div class="p-6">
                <p class="text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this user? This action cannot be undone and all associated data may be lost.
                </p>
                @if($selectedUser)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <dl class="space-y-2">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-medium mr-3">
                                    {{ $selectedUser->initials() }}
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $selectedUser->name }}</dd>
                                </div>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $selectedUser->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Roles</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">
                                    @forelse($selectedUser->roles as $role)
                                        {{ ucfirst($role->name) }}{{ !$loop->last ? ', ' : '' }}
                                    @empty
                                        No roles assigned
                                    @endforelse
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900">
                        Cancel
                    </button>
                    <button type="button" wire:click="delete"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900"
                            wire:loading.attr="disabled">
                        <flux:icon wire:loading.remove wire:target="delete" name="trash" class="w-4 h-4 mr-1.5" />
                        <flux:icon wire:loading wire:target="delete" name="arrow-path" class="w-4 h-4 mr-1.5 animate-spin" />
                        Delete User
                    </button>
                </div>
            </div>
        </div>
    </flux:modal>

    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
</div>