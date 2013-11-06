<? if ($rights): ?>
<table></table>
    <? foreach ($rights as $right): ?>
    <? endforeach; ?>
<? else: ?>
    <?= _('Von ihnen liegen keine Anträge vor') ?>
<? endif; ?>
