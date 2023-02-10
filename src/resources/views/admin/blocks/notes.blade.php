@twillBlockTitle('Notes')
@twillBlockIcon('text')
@twillBlockGroup('capsules')

@formField('input', [
    'name' => 'title',
    'label' => 'Title',
])

@formField('wysiwyg', [
    'name' => 'notes',
    'label' => 'Notes',
    'toolbarOptions' => [
        'bold',
        'italic',
        [ 'script' => 'super' ],
        [ 'script' => 'sub' ],
        'link',
        'clean'
    ],
])
