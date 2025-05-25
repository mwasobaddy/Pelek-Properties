<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

new class extends Component {
    use WithPagination;

    #[State]
    public $showFormModal = false;
    
    #[State]
    public $showDeleteModal = false;
    
    #[State]
    public $modalMode = 'create'; // create, edit, view
    
    #[State]
    public $search = '';
    
    #[State]
    public $selectedRole = null;
    
    #[State]
    public $sortField = 'name';
    
    #[State]
    public $sortDirection = 'asc';
    
    #[State]
    public $isLoading = false;
    
    #[State]
    public $form = [
        'name' => '',
        'permissions' => [],
        'description' => '',
    ];

    #[State]
    public $permissionGroups = [];

    protected $queryString = [
        'search' => ['except' => '']
    ];

    public function mount()
    {
        $this->authorize('manage_roles');
        $this->loadPermissionGroups();
    }

    public function loadPermissionGroups()
    {
        // Group permissions by their prefix (before the first dot)
        $permissions = Permission::orderBy('name')->get();
        $groups = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $group = $parts[0] ?? 'general';
            
            if (!isset($groups[$group])) {
                $groups[$group] = [];
            }
            
            $groups[$group][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => ucwords(str_replace(['_', '.'], ' ', $permission->name)),
            ];
        }
        
        // Sort groups alphabetically
        ksort($groups);
        
        $this->permissionGroups = $groups;
    }

    public function with(): array
    {
        $this->isLoading = true;
        
        try {
            $query = Role::withCount('users')
                ->when($this->search, function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('guard_name', 'like', '%' . $this->search . '%');
                })
                ->orderBy($this->sortField, $this->sortDirection);
            
            return [
                'roles' => $query->paginate(8),
                'totalRoles' => Role::count(),
                'totalPermissions' => Permission::count(),
                'totalUsers' => User::count(),
                'permissionCount' => Permission::count(),
            ];
        } finally {
            $this->isLoading = false;
        }
    }

    public function sort($field): void 
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function create()
    {
        $this->authorize('manage_roles');
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showFormModal = true;
    }

    public function edit(Role $role)
    {
        $this->authorize('manage_roles');
        $this->selectedRole = $role;
        
        $this->form = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('id')->toArray(),
            'description' => $role->description ?? '',
        ];
        
        $this->modalMode = 'edit';
        $this->showFormModal = true;
    }
    
    public function view(Role $role)
    {
        $this->selectedRole = $role;
        
        $this->form = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('id')->toArray(),
            'description' => $role->description ?? '',
        ];
        
        $this->modalMode = 'view';
        $this->showFormModal = true;
    }
    
    public function confirmDelete(Role $role)
    {
        $this->authorize('manage_roles');
        $this->selectedRole = $role;
        $this->showDeleteModal = true;
    }
    
    public function delete()
    {
        $this->authorize('manage_roles');
        
        if ($this->selectedRole) {
            try {
                // Start transaction to ensure clean operation
                DB::beginTransaction();
                
                // Delete the role
                $this->selectedRole->delete();
                
                DB::commit();
                $this->dispatch('notify', type: 'success', message: 'Role deleted successfully.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                logger()->error('Error deleting role', [
                    'role' => $this->selectedRole->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->dispatch('notify', type: 'error', message: 'Error deleting role: ' . $e->getMessage());
            }
            
            $this->showDeleteModal = false;
            $this->selectedRole = null;
        }
    }

    public function save()
    {
        $this->authorize('manage_roles');
        
        // Validate the form
        $this->validate([
            'form.name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->selectedRole?->id)
            ],
            'form.permissions' => 'required|array',
            'form.description' => 'nullable|string|max:1000',
        ], [], [
            'form.name' => 'role name',
            'form.permissions' => 'permissions',
            'form.description' => 'description',
        ]);

        try {
            DB::beginTransaction();
            
            if ($this->modalMode === 'create') {
                // Create a new role
                $role = Role::create([
                    'name' => $this->form['name'],
                    'guard_name' => 'web',
                    'description' => $this->form['description'],
                ]);
                
                // Sync permissions
                $permissions = Permission::whereIn('id', $this->form['permissions'])->get();
                $role->syncPermissions($permissions);
                
                $this->dispatch('notify', type: 'success', message: 'Role created successfully.');
                
            } else {
                // Update existing role
                $this->selectedRole->update([
                    'name' => $this->form['name'],
                    'description' => $this->form['description'],
                ]);
                
                // Sync permissions
                $permissions = Permission::whereIn('id', $this->form['permissions'])->get();
                $this->selectedRole->syncPermissions($permissions);
                
                $this->dispatch('notify', type: 'success', message: 'Role updated successfully.');
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Error saving role', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('notify', type: 'error', message: 'Error saving role: ' . $e->getMessage());
        }
        
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'permissions' => [],
            'description' => '',
        ];
        $this->selectedRole = null;
    }

    public function toggleSelectAllInGroup($group)
    {
        if (!isset($this->permissionGroups[$group])) {
            return;
        }
        
        $groupPermissionIds = collect($this->permissionGroups[$group])->pluck('id')->toArray();
        $allSelected = count(array_intersect($groupPermissionIds, $this->form['permissions'])) === count($groupPermissionIds);
        
        if ($allSelected) {
            // If all are selected, deselect all in this group
            $this->form['permissions'] = array_values(array_diff($this->form['permissions'], $groupPermissionIds));
        } else {
            // Otherwise, select all in this group
            $this->form['permissions'] = array_values(array_unique(array_merge(
                $this->form['permissions'],
                $groupPermissionIds
            )));
        }
    }

    public function selectAll()
    {
        $allPermissionIds = [];
        foreach ($this->permissionGroups as $group => $permissions) {
            $allPermissionIds = array_merge($allPermissionIds, collect($permissions)->pluck('id')->toArray());
        }
        $this->form['permissions'] = $allPermissionIds;
    }

    public function deselectAll()
    {
        $this->form['permissions'] = [];
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
                    <flux:icon name="shield-check" class="w-8 h-8 text-[#02c9c2]" />
                    Roles & Permissions
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage user roles and their assigned permissions
                </p>
            </div>
            
            <button 
                wire:click="create"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                wire:loading.attr="disabled"
            >
                <flux:icon wire:loading.remove name="plus" class="w-5 h-5 mr-2" />
                <flux:icon wire:loading name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                Create New Role
            </button>
        </div>

        <!-- Enhanced Search -->
        <div class="mt-8 sm:flex sm:flex-row-reverse" 
             x-data="{}"
             x-intersect="$el.classList.add('animate-fade-in')">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <flux:icon wire:loading.remove wire:target="search" name="magnifying-glass" class="h-5 w-5 text-gray-400" />
                    <flux:icon wire:loading wire:target="search" name="arrow-path" class="h-5 w-5 text-[#02c9c2] animate-spin" />
                </div>
                <input wire:model.live.debounce.300ms="search" type="search"
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-[#02c9c2] focus:ring-[#02c9c2] sm:text-sm"
                       placeholder="Search roles...">
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Roles Card -->
            <div class="bg-white dark:bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex items-center p-6 transition-all hover:shadow-md group">
                <div class="h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-4">
                    <flux:icon name="user-group" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Roles</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        {{ $totalRoles }}
                    </div>
                </div>
            </div>
            
            <!-- Total Permissions Card -->
            <div class="bg-white dark:bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex items-center p-6 transition-all hover:shadow-md group">
                <div class="h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-4">
                    <flux:icon name="key" class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Permissions</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                        {{ $totalPermissions }}
                    </div>
                </div>
            </div>
            
            <!-- Total Users Card -->
            <div class="bg-white dark:bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex items-center p-6 transition-all hover:shadow-md group">
                <div class="h-12 w-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-4">
                    <flux:icon name="users" class="h-6 w-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                        {{ $totalUsers }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-8">
        <!-- Role Grid with Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($roles as $role)
                <div class="bg-white dark:bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-lg group">
                    <!-- Role Header with Color Based on Role -->
                    <div @class([
                        'h-2 w-full',
                        'bg-blue-600' => $role->name === 'admin',
                        'bg-green-600' => $role->name === 'property_manager',
                        'bg-purple-600' => $role->name === 'content_editor',
                        'bg-[#02c9c2]' => !in_array($role->name, ['admin', 'property_manager', 'content_editor']),
                    ])></div>
                    
                    <div class="p-6 space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ ucfirst($role->name) }}
                                </h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                    {{ $role->users_count }} {{ Str::plural('User', $role->users_count) }}
                                </span>
                            </div>
                            
                            @if($role->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                    {{ $role->description }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="flex flex-wrap gap-1">
                            @php
                                $permissionCount = $role->permissions->count();
                                $displayPermissionCount = min(3, $permissionCount);
                            @endphp
                            
                            @foreach($role->permissions->take($displayPermissionCount) as $permission)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ Str::limit(ucwords(str_replace(['_', '.'], ' ', $permission->name)), 15) }}
                                </span>
                            @endforeach
                            
                            @if($permissionCount > $displayPermissionCount)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    +{{ $permissionCount - $displayPermissionCount }} more
                                </span>
                            @endif
                        </div>

                        <!-- Enhanced Action Buttons -->
                        <div class="flex items-center gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button
                                wire:click.prevent="view({{ $role->id }})"
                                class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-indigo-500 dark:bg-indigo-700/50 rounded-lg p-2"
                                wire:loading.attr="disabled"
                            >
                                <flux:icon wire:loading.remove wire:target="view({{ $role->id }})" name="eye" class="w-5 h-5" />
                                <flux:icon wire:loading wire:target="view({{ $role->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                            </button>
                            
                            <button
                                wire:click.prevent="edit({{ $role->id }})"
                                class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-green-500 dark:bg-green-700/50 rounded-lg p-2"
                                wire:loading.attr="disabled"
                            >
                                <flux:icon wire:loading.remove wire:target="edit({{ $role->id }})" name="pencil-square" class="w-5 h-5" />
                                <flux:icon wire:loading wire:target="edit({{ $role->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                            </button>
                            
                            @if(!in_array($role->name, ['admin', 'property_manager', 'content_editor']))
                                <button
                                    wire:click.prevent="confirmDelete({{ $role->id }})"
                                    class="text-gray-200 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-500 transition-colors duration-150 bg-red-500 dark:bg-red-700/50 rounded-lg p-2"
                                    wire:loading.attr="disabled"
                                >
                                    <flux:icon wire:loading.remove wire:target="confirmDelete({{ $role->id }})" name="trash" class="w-5 h-5" />
                                    <flux:icon wire:loading wire:target="confirmDelete({{ $role->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <!-- Enhanced Empty State -->
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center px-4">
                    <div class="h-24 w-24 rounded-full bg-gradient-to-br from-[#02c9c2]/20 to-[#012e2b]/20 flex items-center justify-center mb-6">
                        <flux:icon name="shield-exclamation" class="w-12 h-12 text-[#02c9c2]" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No roles found</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mb-6">
                        Get started by creating your first role and assigning permissions.
                    </p>
                    <button 
                        wire:click="create"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 transition-all duration-150 shadow-lg"
                    >
                        <flux:icon name="plus" class="w-5 h-5 mr-2" />
                        Create Role
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Loading Overlay -->
        <div wire:loading.delay class="fixed inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
            <div class="flex items-center space-x-4">
                <flux:icon name="arrow-path" class="w-8 h-8 text-[#02c9c2] animate-spin" />
                <span class="text-gray-600 dark:text-gray-300 font-medium">Loading roles...</span>
            </div>
        </div>

        <!-- Pagination -->
        @if($roles->hasPages())
            <div class="px-6 py-4 mt-6 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 rounded-lg">
                {{ $roles->links('components.pagination') }}
            </div>
        @endif
    </div>

    <!-- Role Form Modal -->
    <flux:modal wire:model="showFormModal" class="w-full max-w-4xl !p-0" @close="$wire.resetForm()">
        <div 
            class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden" 
            x-data="{
                isViewMode: function() { return '{{ $modalMode }}' === 'view' },
                init() {
                    // If in view mode, disable all form elements
                    if (this.isViewMode()) {
                        this.$nextTick(() => {
                            this.$el.querySelectorAll('input, select, textarea').forEach(el => {
                                el.disabled = true;
                            });
                        });
                    }
                }
            }"
        >
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    @if($modalMode === 'create')
                        Create New Role
                    @elseif($modalMode === 'edit')
                        Edit Role
                    @else
                        View Role Details
                    @endif
                </h3>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                <div class="space-y-6">
                    <!-- Basic Role Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Basic Information
                        </h4>
                        <div class="space-y-4">
                            <!-- Role Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="user-group" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model="form.name"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Enter role name"
                                    >
                                    @error('form.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Role Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <div class="relative">
                                    <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                        <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <textarea
                                        wire:model="form.description"
                                        rows="3"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Describe the role's purpose and responsibilities"
                                    ></textarea>
                                    @error('form.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div>
                        <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300">
                                Assigned Permissions
                            </h4>
                            @if($modalMode !== 'view')
                                <div class="flex space-x-2">
                                    <button type="button" wire:click="selectAll" 
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                        Select All
                                    </button>
                                    <button type="button" wire:click="deselectAll"
                                        class="text-xs text-gray-600 dark:text-gray-400 hover:underline font-medium">
                                        Deselect All
                                    </button>
                                </div>
                            @endif
                        </div>
                        
                        @error('form.permissions') 
                            <div class="mb-4">
                                <span class="text-red-500 text-sm">{{ $message }}</span> 
                            </div>
                        @enderror
                        
                        <!-- Permission Groups -->
                        <div class="space-y-6">
                            @foreach($permissionGroups as $group => $permissions)
                                <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            {{ ucfirst($group) }}
                                        </h5>
                                        @if($modalMode !== 'view')
                                            <button type="button" wire:click="toggleSelectAllInGroup('{{ $group }}')"
                                                class="text-xs text-[#02c9c2] hover:underline font-medium">
                                                Toggle All
                                            </button>
                                        @endif
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        @foreach($permissions as $permission)
                                            <div class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    id="permission-{{ $permission['id'] }}"
                                                    value="{{ $permission['id'] }}" 
                                                    wire:model="form.permissions"
                                                    @if($modalMode === 'view') disabled @endif
                                                    class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-[#02c9c2] focus:ring-[#02c9c2]"
                                                >
                                                <label for="permission-{{ $permission['id'] }}" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                                    {{ $permission['display_name'] }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- If in view mode, show the users with this role -->
                    @if($modalMode === 'view' && $selectedRole)
                        <div>
                            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Users with this Role
                            </h4>
                            <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-4">
                                @php
                                    $roleUsers = $selectedRole->users()->take(5)->get();
                                    $totalUsers = $selectedRole->users()->count();
                                @endphp
                                
                                @if($totalUsers > 0)
                                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($roleUsers as $user)
                                            <li class="py-3 flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3 text-gray-500 dark:text-gray-400">
                                                    @if($user->profile_photo_path)
                                                        <img src="{{ Storage::disk('property_images')->url($user->profile_photo_path) }}" alt="{{ $user->name }}" class="h-8 w-8 rounded-full object-cover">
                                                    @else
                                                        <flux:icon name="user" class="h-4 w-4" />
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                    
                                    @if($totalUsers > 5)
                                        <div class="mt-3 text-sm text-center text-gray-500 dark:text-gray-400">
                                            + {{ $totalUsers - 5 }} more users with this role
                                        </div>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-3">
                                        No users currently have this role.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-end space-x-3">
                <flux:button variant="primary" wire:click="$toggle('showFormModal')">
                    @if($modalMode === 'view')
                        Close
                    @else
                        Cancel
                    @endif
                </flux:button>
                @if($modalMode !== 'view')
                <flux:button wire:click="save">
                    {{ $modalMode === 'create' ? 'Create Role' : 'Update Role' }}
                </flux:button>
                @endif
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
                    Are you sure you want to delete this role? All users with this role will lose the associated permissions, but their user accounts will not be affected.
                </p>
                @if($selectedRole)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ ucfirst($selectedRole->name) }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedRole->users_count }} users will be affected</p>
                        @if($selectedRole->description)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $selectedRole->description }}</p>
                        @endif
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
                        Delete Role
                    </button>
                </div>
            </div>
        </div>
    </flux:modal>

    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10"></div>
</div>