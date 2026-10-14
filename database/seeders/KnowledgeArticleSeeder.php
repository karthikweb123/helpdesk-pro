<?php

namespace Database\Seeders;

use App\Models\KnowledgeArticle;
use Illuminate\Database\Seeder;

class KnowledgeArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Get connected to the office Wi-Fi',
                'category' => 'Network',
                'summary' => 'Troubleshoot common Wi-Fi connection problems on your work device.',
                'body' => "1. Confirm Wi-Fi is enabled and airplane mode is off.\n2. Select the approved company network and sign in with your work credentials.\n3. If you recently changed your password, forget the saved network and reconnect.\n4. Restart your device and try again.\n\nDo not share your password or connect to an unfamiliar network. If the connection still fails, submit a Network ticket and include your device type and location.",
            ],
            [
                'title' => 'Restart an unresponsive computer safely',
                'category' => 'Hardware',
                'summary' => 'Steps to try when your computer becomes slow or stops responding.',
                'body' => "1. Give the computer a minute in case it is finishing an update.\n2. If possible, save your work and close unresponsive applications.\n3. Use the operating system's normal Restart option.\n4. Wait for the restart to finish before opening your work apps again.\n\nAvoid unplugging a computer while it is updating. If the device does not restart or the problem repeats, open a Hardware ticket and describe any messages on screen.",
            ],
            [
                'title' => 'Fix a work application that will not open',
                'category' => 'Software',
                'summary' => 'A quick checklist for an application that crashes or refuses to start.',
                'body' => "1. Check whether the application has an available update and install it through your approved software center.\n2. Restart the application and then your device.\n3. Confirm you can sign in to the service in your browser, if applicable.\n4. Note the exact error message and when the issue started.\n\nDo not download replacement software from untrusted sites. If you still cannot open the application, submit a Software ticket and attach a screenshot of the error.",
            ],
        ];

        foreach ($articles as $article) {
            KnowledgeArticle::updateOrCreate(['title' => $article['title']], [
                ...$article,
                'user_id' => null,
            ]);
        }
    }
}
