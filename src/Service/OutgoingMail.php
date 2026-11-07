<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Mailer\Mailer;
use Cake\Mailer\Message;

/**
 * Közös levél: megszólítás, és a versenyre hívó levél gombja a leírással.
 */
class OutgoingMail
{
    public const REGISTER_LABEL = 'Jelentkezés a versenyre.';

    public static function greeting(object|array|null $person): string
    {
        $name = trim(User::displayName($person));
        if ($name === '') {
            return 'Kedves!';
        }

        return 'Kedves ' . $name . '!';
    }

    /**
     * @return array{0: string, 1: string} szöveges és HTML törzs
     */
    public static function competitionRegistration(
        object|array|null $person,
        string $details,
        string $url,
        ?string $descriptionHtml,
    ): array {
        $greeting = self::greeting($person);
        $hint = 'Ha nincs belépve, a gomb először a belépésre visz, utána ez az adatlap nyílik meg.';
        $plainDescription = self::plainDescription($descriptionHtml);
        $text = $greeting . "\n\n" . trim($details) . "\n\n" . $hint . "\n" . self::REGISTER_LABEL . "\n" . $url . "\n";
        if ($plainDescription !== '') {
            $text .= "\n" . $plainDescription . "\n";
        }

        $html = '<p style="margin:0 0 16px;">' . self::escape($greeting) . '</p>';
        $html .= self::paragraphs($details);
        $html .= '<p style="margin:16px 0;">' . self::escape($hint) . '</p>';
        $html .= self::button($url, self::REGISTER_LABEL);
        if ($descriptionHtml !== null && trim($descriptionHtml) !== '') {
            $html .= '<div style="margin-top:28px;">' . $descriptionHtml . '</div>';
        }

        return [$text, self::document($html)];
    }

    /**
     * Megszólítás, szöveg és több gomb.
     *
     * @param list<array{0: string, 1: string}> $buttons cím, felirat
     * @return array{0: string, 1: string}
     */
    public static function notice(object|array|null $person, string $details, array $buttons): array
    {
        $greeting = self::greeting($person);
        $hint = 'Ha nincs belépve, a gomb először a belépésre visz, utána a választott oldal nyílik meg.';
        $text = $greeting . "\n\n" . trim($details) . "\n\n" . $hint . "\n";
        foreach ($buttons as [$url, $label]) {
            $text .= "\n" . $label . "\n" . $url . "\n";
        }

        $html = '<p style="margin:0 0 16px;">' . self::escape($greeting) . '</p>';
        $html .= self::paragraphs($details);
        $html .= '<p style="margin:16px 0;">' . self::escape($hint) . '</p>';
        foreach ($buttons as [$url, $label]) {
            $html .= '<div style="margin-top:14px;">' . self::button($url, $label) . '</div>';
        }

        return [$text, self::document($html)];
    }

    public static function send(string $to, string $subject, string $text, ?string $html = null): void
    {
        $mailer = new Mailer('default');
        $mailer->setTo($to)->setSubject($subject);
        if ($html === null) {
            $mailer->deliver($text);

            return;
        }

        $mailer->setEmailFormat(Message::MESSAGE_BOTH);
        $mailer->getMessage()->setBody([
            Message::MESSAGE_TEXT => $text,
            Message::MESSAGE_HTML => $html,
        ]);
        $mailer->getTransport()->send($mailer->getMessage());
    }

    private static function document(string $inner): string
    {
        return '<!DOCTYPE html><html><body style="margin:0;padding:16px;font-family:Arial,sans-serif;font-size:16px;line-height:1.5;color:#222222;">'
            . $inner
            . '</body></html>';
    }

    private static function button(string $url, string $label): string
    {
        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 0;">'
            . '<tr><td style="border-radius:6px;background:#0d6efd;">'
            . '<a href="' . self::escape($url) . '" style="display:inline-block;padding:12px 22px;font-family:Arial,sans-serif;font-size:16px;line-height:1.2;color:#ffffff;text-decoration:none;font-weight:bold;">'
            . self::escape($label)
            . '</a></td></tr></table>';
    }

    private static function paragraphs(string $plain): string
    {
        $plain = trim($plain);
        if ($plain === '') {
            return '';
        }
        $html = '';
        foreach (preg_split("/\n{2,}/", $plain) ?: [] as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }
            $html .= '<p style="margin:0 0 12px;">' . nl2br(self::escape($block), false) . '</p>';
        }

        return $html;
    }

    private static function plainDescription(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }
        $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace("/[ \t]+\n/", "\n", $plain) ?? $plain;
        $plain = preg_replace("/\n{3,}/", "\n\n", $plain) ?? $plain;

        return trim($plain);
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
