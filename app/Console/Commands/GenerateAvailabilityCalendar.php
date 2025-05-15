<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\PropertyBooking;
use App\Models\Availability;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

class GenerateAvailabilityCalendar extends Command
{
    protected $signature = 'calendar:generate';
    protected $description = 'Generate availability calendar for Airbnb properties';

    public function handle()
    {
        $this->info('Generating availability calendar...');

        // First, generate calendar entries for the next 90 days for all Airbnb properties
        $properties = Property::where('listing_type', 'airbnb')->get();
        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addDays(90);

        $bar = $this->output->createProgressBar(count($properties));
        $this->info('Processing ' . count($properties) . ' Airbnb properties');

        foreach ($properties as $property) {
            $bar->advance();
            
            // Get existing calendar from JSON
            $existingCalendar = $property->availability_calendar ?? [];
            
            // Create default availability for the next 90 days
            $period = CarbonPeriod::create($startDate, $endDate);
            
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                
                // Use existing status if available, otherwise mark as available
                $status = $existingCalendar[$dateStr] ?? 'available';
                
                // Create or update calendar entry
                Availability::updateOrCreate(
                    [
                        'property_id' => $property->id,
                        'date' => $dateStr,
                    ],
                    [
                        'status' => $status,
                        'custom_price' => $property->airbnb_price_nightly,
                    ]
                );
            }

            // Update existing booking dates
            $bookings = PropertyBooking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->get();

            foreach ($bookings as $booking) {
                $property->blockDatesForBooking($booking);
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Calendar generation complete!');

        // Clean up old JSON data
        Property::query()->update(['availability_calendar' => null]);
        $this->info('Old calendar data cleaned up.');

        return Command::SUCCESS;
    }
}
