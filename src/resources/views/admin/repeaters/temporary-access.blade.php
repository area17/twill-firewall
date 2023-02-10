@twillRepeaterTitle('Temporary access')
@twillRepeaterTrigger('Add temporary access')
@twillRepeaterGroup('capsules')

@formField('input', [
    'name' => 'code',
    'label' => 'Code',
])

@formField('input', [
    'name' => 'minutes',
    'label' => 'Minutes',
])

@formField('input', [
    'name' => 'valid_until',
    'label' => 'Valid until',
    'disabled' => true,
])
