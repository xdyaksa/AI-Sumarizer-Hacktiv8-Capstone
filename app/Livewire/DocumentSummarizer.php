<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Exception;

class DocumentSummarizer extends Component
{
    use WithFileUploads;

    public $document;
    public $text;
    public $link;
    public $summary;
    public $showFull = false;
    public $loading = false;

    private function extractText($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($path);
                return $pdf->getText();
            } catch (Exception $e) {
                return 'Gagal membaca PDF: ' . $e->getMessage();
            }
        } elseif ($ext === 'docx') {
            try {
                $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
                $text = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                            foreach ($element->getElements() as $e) {
                                if ($e instanceof \PhpOffice\PhpWord\Element\Text) {
                                    $text .= $e->getText() . " ";
                                }
                            }
                        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                            $text .= $element->getText() . " ";
                        }
                    }
                }
                return $text;
            } catch (Exception $e) {
                return 'Gagal membaca DOCX: ' . $e->getMessage();
            }
        } elseif ($ext === 'txt') {
            return file_get_contents($path);
        }
        return '';
    }

    public function summarize()
    {
        // Tampilkan loading toast sebelum proses
        $this->dispatch('swal:loading');

        $this->loading = true;
        $inputText = null;

        if ($this->text) {
            $inputText = $this->text;
        } elseif ($this->link) {
            $inputText = $this->extractTextFromUrl($this->link);
        } elseif ($this->document) {
            $path = $this->document->getRealPath();
            $inputText = $this->extractText($path);
        }

        if ($inputText) {
            $summary = $this->summarizeWithAI($inputText);
            $this->summary = $summary ?: 'Gagal mendapatkan ringkasan dari AI.';
        } else {
            $this->summary = 'Tidak ada input.';
        }

        $this->loading = false;
        // Tutup loading toast setelah proses selesai
        $this->dispatch('swal:close');
    }

    private function extractTextFromUrl($url)
    {
        try {
            $client = new Client();
            $response = $client->get($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ]
            ]);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);
            $paragraphs = $crawler->filter('p')->each(fn($node) => $node->text());
            $text = implode("\n", $paragraphs);
            return $text ?: 'Tidak ditemukan konten artikel.';
        } catch (Exception $e) {
            return 'Gagal mengambil artikel: ' . $e->getMessage();
        }
    }

    public function showMore()
    {
        $this->showFull = true;
        $this->summarize();
    }

    public function clearInput()
    {
        $this->text = null;
        $this->link = null;
        $this->document = null;
        $this->summary = null;
        $this->showFull = false;
        $this->loading = false;
    }

    private function summarizeWithAI($inputText)
    {
        $apiToken = env('REPLICATE_API_TOKEN');
        $modelVersion = 'a325a0cacfb0aa9226e6bad1abe5385f1073f4c7f8c36e52ed040e5409e6c034';
        $prompt = "Ringkas dokumen berikut dalam bahasa Indonesia yang mudah dipahami. Jangan hanya menyalin atau menata ulang isi dokumen, tapi rangkum inti, manfaat utama, dan dampak dari isi dokumen dalam maksimal 5-7 kalimat. Pastikan hasilnya adalah ringkasan padat, bukan daftar poin atau penjelasan slide:\n\n" . $inputText;

        try {
            $client = new Client();
            $response = $client->post('https://api.replicate.com/v1/models/ibm-granite/granite-3.3-8b-instruct/predictions', [
                'headers' => [
                    'Authorization' => 'Token ' . $apiToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Prefer' => 'wait',
                ],
                'json' => [
                    'input' => [
                        'prompt' => $prompt,
                        'top_p' => 0.9,
                        'max_tokens' => 1024,
                        'min_tokens' => 0,
                        'temperature' => 0.6,
                        'presence_penalty' => 0,
                        'frequency_penalty' => 0
                    ],
                ],
            ]);
            $result = json_decode($response->getBody(), true);

            $predictionId = $result['id'] ?? null;
            if (!$predictionId) {
                return 'Gagal memulai prediksi AI.';
            }

            // Loop untuk polling dengan batasan waktu
            for ($i = 0; $i < 30; $i++) {
                sleep(2);
                $statusResponse = $client->get("https://api.replicate.com/v1/predictions/{$predictionId}", [
                    'headers' => [
                        'Authorization' => 'Token ' . $apiToken,
                        'Accept' => 'application/json',
                    ],
                ]);
                $statusResult = json_decode($statusResponse->getBody(), true);

                $status = $statusResult['status'] ?? '';
                if ($status === 'succeeded') {
                    // Jika $statusResult['output'] adalah array:
                    if (is_array($statusResult['output'])) {
                        $outputText = implode('', $statusResult['output']); // Gabung tanpa spasi
                    } else {
                        $outputText = $statusResult['output'];
                    }
                    $outputText = str_replace('**', '<br><br>', $outputText);
                    $this->summary = $outputText;
                    return $outputText;
                } elseif ($status === 'failed') {
                    return 'AI gagal melakukan ringkasan.';
                }
            }
            return 'Timeout menunggu hasil ringkasan AI.';
        } catch (Exception $e) {
            return 'Gagal menghubungi AI: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.document-summarizer');
    }
}
