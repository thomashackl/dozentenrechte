<? if ($rights): ?>
    <table class="default">
        <caption>
            <?= _('Gestellte Dozentenanträge') ?>
        </caption>
        <thead>
            <tr>
                <th><?= _('Benutzer') ?></th>
                <th><?= _('Einrichtung') ?></th>
                <th><?= _('Von') ?></th>
                <th><?= _('Bis') ?></th>
                <th><?= _('Status') ?></th>
                <th><?= _('Aktion') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($rights as $right): ?>
                <tr>
                    <td><?= htmlReady($right->user->getFullname()) ?></td>
                    <td><?= htmlReady($right->institute->name) ?></td>
                    <td><?= $right->begin ? date('d.m.Y', $right->begin) : _('Unbegrenzt'); ?></td>
                    <td><?= $right->end == PHP_INT_MAX ? date('d.m.Y', $right->end) : _('Unbegrenzt'); ?></td>
                    <td><?= $right->verify ? _('Bestätigt') : _('Wartend') ?></td>
                    <td><?= $right->verify ? : \Studip\Button::create(_('Antrag zurückziehen'), array('reject' => $right->id)) ?></td>
                </tr>
            <? endforeach; ?>
        </tbody>
    </table>
<? else: ?>
    <?= _('Von ihnen liegen keine Anträge vor') ?>
<? endif; ?>
