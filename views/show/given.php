<? if ($rights): ?>
<table></table>
    <? foreach ($rights as $right): ?>
    <? endforeach; ?>
<? else: ?>
    <?= _('Von ihnen liegen keine Antr�ge vor') ?>
<? endif; ?>
