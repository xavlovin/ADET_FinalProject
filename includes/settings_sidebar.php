<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navSections = [
    [
        'category' => 'Account Details',
        'items' => [
            ['url' => 'user_ProfileSettings.php', 'label' => 'Profile & Personal Info']
        ]
    ],
    [
        'category' => 'Security',
        'items' => [
            ['url' => 'user_SecuritySettings.php', 'label' => 'Change Password']
        ]
    ],
    [
        'category' => 'Address & Contact Info',
        'items' => [
            ['url' => 'user_AddressSettings.php', 'label' => 'Change Address & Contact Info']
        ]
    ]
];
?>
<div class="flex-shrink-0"
    style="width: 247px; background: rgba(255,255,255,0.55); border: 1.111px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); border-radius: 24px; overflow: hidden; position: relative;">
    <?php foreach ($navSections as $index => $section): ?>
        <div>
            <div style="padding: 20px 21px 0;">
                <p
                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; margin-bottom: 10px;">
                    <?= htmlspecialchars($section['category']) ?>
                </p>
                <?php foreach ($section['items'] as $item): ?>
                    <?php $isActive = ($currentPage === $item['url']); ?>
                    <a href="<?= $item['url'] ?>" class="block w-full text-left transition-opacity hover:opacity-80"
                        style="font-family: var(--font-sans); font-size: 13px; font-weight: <?= $isActive ? '700' : '400' ?>; color: #6b7268; text-decoration: none; padding: 4px 0 20px;">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if ($index < count($navSections) - 1): ?>
                <div
                    style="height: 1px; background: linear-gradient(to right, transparent, rgba(176,138,74,0.3), transparent); margin: 0 11px;">
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>