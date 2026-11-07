<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Competition Entity
 *
 * @property int $id
 * @property string $name
 * @property string|null $lunch_title
 * @property string|null $special_lunch_title
 * @property string|null $competition_pipe_title_1
 * @property string|null $competition_pipe_title_2
 * @property string|null $competition_pipe_title_3
 * @property string|null $extra_title_1
 * @property string|null $extra_title_2
 * @property string|null $extra_title_3
 * @property string $comment
 * @property \Cake\I18n\Date $date_from
 * @property \Cake\I18n\Date $date_deadline
 * @property string|null $description
 * @property int|null $registration_count
 * @property bool $visible
 * @property int $pos
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Registration[] $registrations
 */
class Competition extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'lunch_title' => true,
        'special_lunch_title' => true,
        'competition_pipe_title_1' => true,
        'competition_pipe_title_2' => true,
        'competition_pipe_title_3' => true,
        'extra_title_1' => true,
        'extra_title_2' => true,
        'extra_title_3' => true,
        'comment' => true,
        'date_from' => true,
        'date_deadline' => true,
        'description' => true,
        'registration_count' => true,
        'visible' => true,
        'pos' => true,
        'created' => true,
        'modified' => true,
        'registrations' => true,
    ];

    /**
     * A nevezési határidő napja még nyitott. Csak a korábbi nap számít lezártnak.
     */
    public function registrationClosed(): bool
    {
        return $this->date_deadline !== null && $this->date_deadline->isPast();
    }

    /**
     * A leírás megjelenítésre. A sima szöveg marad szöveg, a szerkesztő HTML-jét
     * csak a megengedett formázás hagyja meg.
     */
    public function descriptionHtml(): ?string
    {
        $description = trim((string)$this->description);
        if ($description === '') {
            return null;
        }
        if (!str_contains($description, '<')) {
            return '<p>' . nl2br(h($description), false) . '</p>';
        }

        $allowed = '<p><br><strong><b><em><i><u><s><ul><ol><li><a><table><thead><tbody><tfoot><tr><th><td><h1><h2><h3><h4><h5><h6><blockquote><span><div><pre><code><hr>';
        $html = strip_tags($description, $allowed);
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $html) ?? $html;
        $html = preg_replace('/\s+(href|src)\s*=\s*(["\'])\s*(javascript|data):[^"\']*\2/iu', '', $html) ?? $html;
        $plain = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($plain === '' && !preg_match('/<(table|hr)\b/i', $html)) {
            return null;
        }

        return $html;
    }
}
