<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Notifications\Notification; // Import for notifications

    class NewsletterSection extends Component
    {
        public string $email = ''; // Property to hold the email input

        // Validation rules for the email field
        protected $rules = [
            'email' => 'required|email|max:255', // Email is required, must be a valid email format, max 255 chars
        ];

        // Method to handle the subscription logic when the form is submitted
        public function subscribe()
        {
            $this->validate(); // Run validation defined in $rules

            // TODO: Implement the actual logic for saving the email to your database or a newsletter service.
            // Example:
            // \App\Models\NewsletterSubscription::create(['email' => $this->email]);

            // Send a success notification using Filament's notification system
            Notification::make()
                ->title('Berhasil berlangganan newsletter! (simulasi)') // Success message
                ->success() // Green color for success
                ->send();

            $this->email = ''; // Clear the email input field after successful submission
        }

        // The render method defines what HTML is displayed by this Livewire component
        public function render()
        {
            return view('livewire.newsletter-section');
        }
    }