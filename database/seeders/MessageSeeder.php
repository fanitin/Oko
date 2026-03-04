<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            'Hey, how are you doing today?',
            'I\'m great, thanks for asking! Just finished working on a new project.',
            'That sounds exciting! What kind of project is it?',
            'It\'s a chat application with end-to-end encryption. Pretty cool stuff!',
            'Wow, that\'s impressive. How long have you been working on it?',
            'About three weeks now. Still have a lot to do though.',
            'Do you need any help with it? I have some experience with real-time apps.',
            'That would be awesome! I could definitely use some help with the WebSocket implementation.',
            'Sure, I\'d be happy to help. When do you want to meet?',
            'How about tomorrow afternoon? Say around 2 PM?',
            'Perfect! I\'ll see you then.',
            'Great! Looking forward to it.',
            'By the way, have you seen the new design mockups?',
            'Not yet, where did you post them?',
            'I sent them to the group chat earlier today.',
            'Oh, I must have missed that. Let me check.',
            'No worries, take your time.',
            'Found them! These look really good.',
            'Thanks! I spent quite a bit of time on them.',
            'It shows. The attention to detail is impressive.',
            'I appreciate that. Feedback is always welcome.',
            'Have you thought about adding dark mode?',
            'Actually, yes! It\'s already implemented.',
            'Excellent. Dark mode is a must-have these days.',
            'Absolutely. Users love having the option.',
            'What about mobile responsiveness?',
            'That\'s next on my list. Should be done by next week.',
            'Sounds like you have everything under control.',
            'Trying my best! It\'s a lot of work but very rewarding.',
            'I bet. Keep up the good work!',
            'Thanks for the encouragement. It means a lot.',
            'Anytime! We\'re a team after all.',
            'Speaking of which, did everyone confirm for the meeting?',
            'Yes, everyone\'s on board. Should be a productive session.',
            'Great! I have some important updates to share.',
            'Can\'t wait to hear them.',
            'It\'s about the timeline. We might need to adjust some deadlines.',
            'That\'s understandable. Quality over speed, right?',
            'Exactly. No point rushing if we compromise quality.',
            'I completely agree.',
            'How\'s your part of the project coming along?',
            'Pretty good actually. Just ironing out some bugs.',
            'Need any help with debugging?',
            'I might take you up on that offer if I get stuck.',
            'Just let me know. I\'m here to help.',
            'Will do. Thanks!',
            'By the way, have you tested the latest build?',
            'Not yet, is it ready?',
            'Yes, I pushed it to staging this morning.',
            'I\'ll test it this afternoon and let you know.',
            'Awesome, thanks!',
            'No problem. That\'s what I\'m here for.',
            'I really appreciate your thoroughness.',
            'Just doing my job! Quality assurance is important.',
            'It definitely is. Your work makes a huge difference.',
            'That\'s nice of you to say.',
            'I mean it. The project wouldn\'t be the same without you.',
            'Well, thank you. That really means a lot.',
            'So, what are your plans for the weekend?',
            'Not much, probably just relax and catch up on some reading.',
            'Sounds nice. What are you reading?',
            'A book about software architecture patterns.',
            'Oh cool! Always good to keep learning.',
            'Absolutely. The tech world moves so fast.',
            'Tell me about it. Sometimes it\'s hard to keep up.',
            'That\'s why continuous learning is so important.',
            'You\'re right. Any other books you\'d recommend?',
            'Depends on what you\'re interested in.',
            'I\'m really into system design lately.',
            'Then you should check out "Designing Data-Intensive Applications".',
            'I\'ve heard of that one! Is it good?',
            'One of the best books on the topic, in my opinion.',
            'Great, I\'ll add it to my reading list.',
            'You won\'t regret it. It\'s very comprehensive.',
            'Thanks for the recommendation!',
            'Anytime. Always happy to share good resources.',
            'Well, I should probably get back to work.',
            'Yeah, me too. Let\'s catch up later.',
            'Sounds good. Talk to you soon!',
            'Take care!',
            'You too!',
            'Oh wait, one more thing.',
            'What\'s up?',
            'Did you remember to push your latest changes?',
            'Oops, I forgot! Thanks for reminding me.',
            'No worries, happens to everyone.',
            'Pushing them now...',
            'Perfect. I\'ll pull them in a bit.',
            'All done!',
            'Great, thanks!',
            'Alright, now I really need to get back to work.',
            'Haha, me too. For real this time.',
            'See you later!',
            'Later!',
            'Actually, quick question...',
            'Sure, what is it?',
            'What\'s the password for the staging server again?',
            'Check the team wiki, it should be there.',
            'Oh right, I always forget about the wiki.',
            'We should probably use a password manager.',
            'That\'s a great idea. I\'ll look into setting one up.',
            'Let me know if you need help with that.',
            'Will do. Thanks again!',
            'No problem. Now, seriously, back to work!',
            'Right! Bye!',
        ];

        $userIds = [1, 2];
        $chatId = 1;
        $baseTime = now()->subDays(5);

        foreach ($messages as $index => $text) {
            $userId = $userIds[$index % 2];
            $timestamp = $baseTime->copy()->addMinutes($index * rand(5, 30));

            $message = Message::create([
                'chat_id' => $chatId,
                'user_id' => $userId,
                'body' => $text,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            if ($index > 0 && rand(1, 10) > 8) {
                $message->edited_at = $timestamp->copy()->addMinutes(rand(1, 5));
                $message->save();
            }

            if ($index > 5 && rand(1, 10) > 7) {
                $replyToId = Message::where('chat_id', $chatId)
                    ->where('id', '<', $message->id)
                    ->inRandomOrder()
                    ->first()?->id;

                if ($replyToId) {
                    $message->reply_for_message_id = $replyToId;
                    $message->save();
                }
            }
        }
    }
}
