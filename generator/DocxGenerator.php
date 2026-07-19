<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;

class DocxGenerator
{
    private PhpWord $phpWord;

    public function __construct()
    {
        $this->phpWord = new PhpWord();

        $this->phpWord->setDefaultFontName('Arial');
        $this->phpWord->setDefaultFontSize(10);

        $this->phpWord->addTitleStyle(1, ['size' => 20, 'bold' => true, 'color' => '1a1a2e']);
        $this->phpWord->addTitleStyle(2, ['size' => 14, 'bold' => true, 'color' => '51459e']);
        $this->phpWord->addTitleStyle(3, ['size' => 12, 'bold' => true, 'color' => '333333']);

        $this->phpWord->addParagraphStyle('pNormal', [
            'align'   => 'left',
            'spaceAfter' => 120,
            'spaceBefore' => 60,
            'lineHeight' => 1.3,
        ]);
    }

    public function generate(string $html): string
    {
        $html = $this->sanitizeHtml($html);
        $html = $this->enhanceHtml($html);

        $section = $this->phpWord->addSection($this->getSectionConfig());

        $useErrors = libxml_use_internal_errors(true);
        Html::addHtml($section, $html, false, 'pNormal');
        libxml_use_internal_errors($useErrors);

        $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
        $this->phpWord->save($tempFile, 'Word2007');
        $content = file_get_contents($tempFile);
        unlink($tempFile);

        $this->phpWord = new PhpWord();
        return $content;
    }

    private function getSectionConfig(): array
    {
        return [
            'marginLeft'   => 1440,
            'marginRight'  => 1440,
            'marginTop'    => 1440,
            'marginBottom' => 1440,
            'pageSizeW'    => 12240,
            'pageSizeH'    => 15840,
        ];
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('/<!DOCTYPE[^>]*>/i', '', $html);
        $html = preg_replace('/<html[^>]*>/i', '', $html);
        $html = preg_replace('/<\/html>/i', '', $html);
        $html = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $html);
        $html = preg_replace('/<body[^>]*>/i', '', $html);
        $html = preg_replace('/<\/body>/i', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        return trim($html);
    }

    private function enhanceHtml(string $html): string
    {
        $html = preg_replace('/<h1>/i', '<h1 style="font-size:20pt;color:#1a1a2e;">', $html);
        $html = preg_replace('/<h2>/i', '<h2 style="font-size:14pt;color:#51459e;border-bottom:1px solid #e0e0e0;padding-bottom:4px;">', $html);
        $html = preg_replace('/<h3>/i', '<h3 style="font-size:12pt;color:#333;">', $html);
        $html = preg_replace('/<table>/i', '<table style="width:100%;border-collapse:collapse;margin:10px 0;font-size:9pt;">', $html);
        $html = preg_replace('/<th>/i', '<th style="background:#51459e;color:white;padding:6px 8px;text-align:left;font-weight:bold;">', $html);
        $html = preg_replace('/<td>/i', '<td style="padding:5px 8px;border-bottom:1px solid #e0e0e0;">', $html);
        $html = preg_replace('/<p>/i', '<p style="font-size:10pt;line-height:1.5;margin:4px 0;">', $html);
        $html = preg_replace('/<li>/i', '<li style="font-size:10pt;margin-bottom:2px;">', $html);
        return $html;
    }
}
