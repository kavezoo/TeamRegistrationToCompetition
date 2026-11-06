<?php
/**
 * A fejléc jobb felső képe. A már eltárolt Google-profilkép, különben a felhasználó ikon.
 *
 * @var \App\View\AppView $this
 * @var string $alt
 */
use App\Model\Entity\User;
use Cake\ORM\TableRegistry;

$alt = (string)($alt ?? '');
if ($this->get('accountAvatarResolved') !== true) {
    $url = null;
    $identity = $this->getRequest()->getAttribute('identity');
    $id = is_array($identity) || $identity instanceof \ArrayAccess ? ($identity['id'] ?? null) : null;
    if ($id !== null && $id !== '') {
        try {
            $account = TableRegistry::getTableLocator()->get('SocialAccounts')->find()
                ->select(['id', 'provider', 'avatar'])
                ->where([
                    'SocialAccounts.user_id' => $id,
                    'SocialAccounts.provider' => 'google',
                ])
                ->orderBy(['SocialAccounts.modified' => 'DESC'])
                ->first();
            $url = User::avatarUrl(['social_accounts' => $account === null ? [] : [$account]]);
        } catch (\Throwable) {
            $url = null;
        }
    }
    $this->set('accountAvatarResolved', true);
    $this->set('accountAvatarUrl', $url);
}
$url = $this->get('accountAvatarUrl');
if (is_string($url) && $url !== '') {
    echo '<img src="' . h($url) . '" alt="' . h($alt) . '" referrerpolicy="no-referrer">';

    return;
}
echo $this->Icon->filled('user', ['alt' => $alt]);
