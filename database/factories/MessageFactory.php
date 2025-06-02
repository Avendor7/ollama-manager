<?php

namespace Database\Factories;

use App\Models\ChatSession;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->word(),
            'role' => $this->faker->word(),
            'token_count' => Str::random(10),
            'metadata' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'chat_session_id' => ChatSession::factory(),
        ];
    }
}
