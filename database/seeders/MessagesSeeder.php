<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use Faker\Factory as Faker;

class MessagesSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $replyIds = [];
        $now = now();
        for ($i = 1; $i <= 100; $i++) {
            $userId = $i % 2 === 0 ? 1 : 2;
            $body = $faker->sentence(rand(5, 30));
            $createdAt = $now->copy()->subMinutes(100 - $i)->subSeconds(rand(0, 59));
            $edited = rand(0, 1) ? $createdAt->copy()->addMinutes(rand(1, 10)) : null;
            $replyFor = (count($replyIds) > 0 && rand(0, 3) === 0) ? $faker->randomElement($replyIds) : null;
            $message = Message::create([
                'chat_id' => 1,
                'user_id' => $userId,
                'body' => $body,
                'created_at' => $createdAt,
                'updated_at' => $edited ?? $createdAt,
                'edited_at' => $edited,
                'reply_for_message_id' => $replyFor,
            ]);
            $replyIds[] = $message->id;
        }
    }
}
