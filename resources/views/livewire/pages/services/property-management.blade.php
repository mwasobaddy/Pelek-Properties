<?php

use function Livewire\Volt\{state};

?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Property Management Services</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">Professional property management solutions for landlords and property owners</p>
    </div>

    <div class="grid md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="w-12 h-12 bg-[#02c9c2] rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Tenant Management</h3>
            <ul class="text-gray-600 dark:text-gray-300 space-y-2">
                <li>• Tenant screening</li>
                <li>• Lease preparation</li>
                <li>• Rent collection</li>
                <li>• Regular inspections</li>
            </ul>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="w-12 h-12 bg-[#02c9c2] rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2m4-10a4 4 0 100-8 4 4 0 000 8z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Property Maintenance</h3>
            <ul class="text-gray-600 dark:text-gray-300 space-y-2">
                <li>• 24/7 emergency response</li>
                <li>• Preventive maintenance</li>
                <li>• Vendor management</li>
                <li>• Regular reports</li>
            </ul>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="w-12 h-12 bg-[#02c9c2] rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Financial Management</h3>
            <ul class="text-gray-600 dark:text-gray-300 space-y-2">
                <li>• Rent collection</li>
                <li>• Expense tracking</li>
                <li>• Monthly statements</li>
                <li>• Tax documentation</li>
            </ul>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-[#012e2b] text-white rounded-lg p-8 text-center">
        <h2 class="text-2xl font-semibold mb-4">Ready to get started?</h2>
        <p class="text-gray-300 mb-6">Let us handle the day-to-day management of your property portfolio</p>
        <a href="{{ route('contact') }}" class="inline-block bg-[#02c9c2] hover:bg-[#02a8a2] text-white font-medium px-6 py-3 rounded-md transition-colors duration-300">
            Contact Our Team
        </a>
    </div>
</div>
