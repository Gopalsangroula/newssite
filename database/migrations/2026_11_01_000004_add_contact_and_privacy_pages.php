<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $author = DB::table('users')->min('id');

        foreach ($this->pages() as $page) {
            if (DB::table('pages')->where('slug', $page['slug'])->exists()) {
                continue;
            }

            DB::table('pages')->insert([
                ...$page,
                'created_by' => $author,
                'published_at' => $page['status'] === 'published' ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations. The pages stay, because they may have been edited by then.
     */
    public function down(): void
    {
        //
    }

    private function pages(): array
    {
        return [
            [
                'title' => 'सम्पर्क गर्नुहोस्',
                'slug' => 'contact',
                'status' => 'published',
                'content' => <<<'HTML'
                    <h2>केन्द्रीय कार्यालय</h2>
                    <p>केत्तकुम लिबाङ पाङ्पत सयङ<br>विर्तामोड - १, झापा</p>
                    <h2>फोन</h2>
                    <p><a href="tel:+9779851129961">9851129961</a></p>
                    <h2>इमेल</h2>
                    <p><a href="mailto:info@appharu.com">info@appharu.com</a></p>
                    HTML,
            ],
            [
                'title' => 'गोपनीयता नीति',
                'slug' => 'privacy-policy',
                'status' => 'draft',
                'content' => <<<'HTML'
                    <p>केत्तकुम लिबाङ पाङ्पत सयङले यो वेबसाइट प्रयोग गर्ने व्यक्तिको गोपनीयताको सम्मान गर्दछ। यस पृष्ठमा हामीले कुन जानकारी राख्छौँ र त्यसको प्रयोग कसरी हुन्छ भन्ने उल्लेख छ।</p>
                    <h2>हामीले राख्ने जानकारी</h2>
                    <p>यो वेबसाइट हेर्दा हामी तपाईँको आईपी (IP) ठेगाना, ब्राउजर वा हेरेका पृष्ठको अभिलेख राख्दैनौँ। प्रत्येक समाचार कति पटक पढियो भन्ने कुल सङ्ख्या मात्र गनिन्छ, जसबाट पाठक चिनिँदैन।</p>
                    <p>समाचारमा टिप्पणी लेख्दा तपाईँले दिएको नाम, इमेल र टिप्पणी राखिन्छ।</p>
                    <h2>जानकारीको प्रयोग</h2>
                    <p>स्वीकृत भएपछि तपाईँको नाम र टिप्पणी सार्वजनिक देखिन्छन्। इमेल सार्वजनिक गरिँदैन, वेबसाइट व्यवस्थापन गर्ने अधिकृत व्यक्तिले मात्र हेर्न सक्छन्। स्प्याम रोक्न र वेबसाइट सुरक्षित राख्न आईपी ठेगाना छोटो समयका लागि मात्र प्रयोग हुन्छ।</p>
                    <h2>अन्य सेवाहरू</h2>
                    <p>यो वेबसाइटमा सामग्री सेयर गर्न फेसबुक र ShareThis जस्ता बाहिरी सेवा प्रयोग गरिएका छन्। यी सेवाले आफ्नै गोपनीयता नीति अनुसार जानकारी राख्न सक्छन्।</p>
                    <h2>सम्पर्क</h2>
                    <p>यस नीतिबारे प्रश्न भएमा <a href="/pages/contact">सम्पर्क पृष्ठ</a> मार्फत हामीलाई सम्पर्क गर्नुहोस्।</p>
                    HTML,
            ],
        ];
    }
};
