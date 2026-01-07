<?php

namespace App\Services;

class ContentBuilderService
{
    public function buildFromFunnelData(array $data): array
    {
        $sections = [];

        // Main heading
        $sections[] = [
            'type' => 'heading',
            'data' => [
                'text' => "{$data['coupleName1']} & {$data['coupleName2']}",
            ],
            'order' => 0,
        ];

        // Event details
        $sections[] = [
            'type' => 'text',
            'data' => [
                'text' => "מיקום: {$data['eventLocation']}\nתאריך: {$data['eventDate']}" . 
                    (isset($data['hebrewDate']) ? "\nתאריך עברי: {$data['hebrewDate']}" : ''),
            ],
            'order' => 1,
        ];

        // Schedule
        $sections[] = [
            'type' => 'heading',
            'data' => ['text' => 'לוח זמנים'],
            'order' => 2,
        ];

        $sections[] = [
            'type' => 'text',
            'data' => [
                'text' => "קבלת פנים: {$data['receptionTime']}\nטקס: {$data['ceremonyTime']}",
            ],
            'order' => 3,
        ];

        // Optional sections
        $order = 4;
        if (isset($data['sections'])) {
            foreach ($data['sections'] as $key => $section) {
                if (!isset($section['enabled']) || !$section['enabled']) {
                    continue;
                }

                if (isset($section['heading']) && $section['heading']) {
                    $sections[] = [
                        'type' => 'heading',
                        'data' => ['text' => $section['heading']],
                        'order' => $order++,
                    ];
                }

                if (isset($section['text']) && $section['text']) {
                    $sections[] = [
                        'type' => 'text',
                        'data' => ['text' => $section['text']],
                        'order' => $order++,
                    ];
                }

                if (isset($section['image']) && $section['image']) {
                    $sections[] = [
                        'type' => 'image',
                        'data' => [
                            'url' => $section['image'],
                            'alt' => $section['heading'] ?? '',
                        ],
                        'order' => $order++,
                    ];
                }

                if (isset($section['buttons']) && is_array($section['buttons'])) {
                    $sections[] = [
                        'type' => 'buttons',
                        'data' => ['buttons' => $section['buttons']],
                        'order' => $order++,
                    ];
                }
            }
        }

        // Couple photo
        if (isset($data['couplePhoto']) && $data['couplePhoto']) {
            $photoUrl = is_string($data['couplePhoto']) 
                ? $data['couplePhoto'] 
                : (isset($data['generatedImage']) ? $data['generatedImage'] : null);

            if ($photoUrl) {
                $sections[] = [
                    'type' => 'image',
                    'data' => [
                        'url' => $photoUrl,
                        'alt' => "{$data['coupleName1']} & {$data['coupleName2']}",
                    ],
                    'order' => $order++,
                ];
            }
        }

        return $sections;
    }
}

