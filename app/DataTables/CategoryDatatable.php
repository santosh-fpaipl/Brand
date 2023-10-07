<?php

namespace App\DataTables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Category as Model;
use Fpaipl\Panel\Datatables\ModelDatatable;

class CategoryDatatable extends ModelDatatable
{
    const SORT_SELECT_DEFAULT = 'name#asc';
    /**
     * It is used to store batch uuid in cache with in this key.
     */
    const IMPORT_BATCH_UUID = 'fabric_category_batch_uuid';

    
    public static function baseQuery($model): Builder
    {
        return $model::with('parent');
    }

    public function selectOptions($field): Collection
    {
        switch ($field) {
            case 'parent_id': return Model::TopRecords()->get();
            default: return collect();
                // return collect([
                //     (object) [
                //         'id' => 'twitter',
                //         'name' => 'twitter.com'
                //     ],
                //     (object) [
                //         'id' => 'google',
                //         'name' => 'google.com'
                //     ]
                // ]);
        }
    }

    public function topButtons(): array
    {
        return array_merge(
            array(
                'add_new' => [
                    'show' => [
                        'active' => true,
                        'trash' => false,
                    ],
                    'label' => 'Create',
                    'type' => 'buttons.action-link',
                    'style' => 'mw-100px me-2',
                    'route' => 'fabriccategories.create',
                    'function' => ''
                ],
            ),
            parent::topButtonsPart1(),
            parent::topButtonsPart2()  
        );
    }

    public function tableButtons(): array
    {
        return array(
            'view' => [
                'show' => [
                    'active' => $this->features()['row_actions']['show']['view']['active'],
                    'trash' => $this->features()['row_actions']['show']['view']['trash'],
                ],
                'label' => 'View',
                'type' => 'buttons.action-link', // action-link - for new page && action-toggle to collapse
                'style' => '',
                'route' => 'fabriccategories.show', // categories.show - for new page
                'function' => '',
                'confirm' => false, // This boolean value control that confirm modal will show or not
            ],
            'edit' => [
                'show' => [
                    'active' => $this->features()['row_actions']['show']['edit'],
                    'trash' => false, // Will always be false because we can't edit on trash page.
                ],
                'label' => 'Edit',
                'type' => 'buttons.action-link',
                'style' => '',
                'route' => 'fabriccategories.edit',
                'function' => '',
                'confirm' => false,
            ],
            'delete' => [
                'show' => [
                    'active' => $this->features()['row_actions']['show']['delete'],
                    'trash' => false, //Will always be false because we can't delete on trash page.
                ],
                'label' => 'Delete',
                'type' => 'buttons.action-delete',
                'style' => '',
                'route' => 'fabriccategories.destroy',
                'function' => '',
                'confirm' => false, // To open confirm mode, we have to set  type' => 'buttons.action-btn' and 'confirm' => true
            ],
            'adv_delete' => [
                'show' => [
                    'active' => true,
                    'trash' => false, //Will always be false because we can't delete on trash page.
                ],
                'label' => 'Adv Delete',
                'type' => 'buttons.action-link',
                'style' => '',
                'route' => 'fabriccategory.advance.delete',
                'function' => '',
                'confirm' => false, // To open confirm mode, we have to set  type' => 'buttons.action-btn' and 'confirm' => true
            ]
        );
    }

    /**
     *  'key_name' => 
     *   [
     *     'name' => 'string', // Field name of database's table
     *     'labels' => [
     *         'table' => 'string', // Used as a label of field in list , add, edit page.  
     *         'export' => 'string' // Used as a label of field in excel file of export.
     *     ],
     *     'cell' => [
     *         'view' => 'string', // It denotes the component name used to show the field value on list and show page. 
     *         'function' => 'string' // If we provide value in it then this value will be used as a function name to get the value.
     *     ],
     *     'viewable' => [
     *         'active' => boolean, // This control that this field will show or not on list page.
     *         'trash' => boolean // This control that this field will show or not on trash page.
     *     ],
     *     'expandable' => [
     *         'active' => boolean, // This control that this field will show or not on list page in expandable section.
     *         'trash' => boolean // This control that this field will show or not on trash page in expandable section.
     *     ],
     *     'sortable' => boolean, // This control that sortable select section (like Asc or Desc) with this field will be created or not.
     *     'filterable' => [
     *         'active' => boolean, // This control that this field will show on not in filter section on list page
     *         'trash' => boolean // This control that this field will show on not in filter section on trash page
     *     ],
     *     'exportable' => [ // This control that this field will show or not in excel file of export.
     *         'active' => boolean,
     *         'trash' => boolean
     *     ],
     *     'artificial' => boolean, // This control that this field will show or not in Add/Edit page.
     *     'fillable' => [
     *         'type' => 'string', // Used as a type of input field for Add/Edit page.
     *         'style' => 'string', // Used as a class name in input field
     *         'placeholder' => 'string', // Used as a placeholder value of input field.
     *         'component' => 'string', // Used for input field creation for Add/Edit page.
     *         'attributes' => ['string'] // Used as attributes of input field.
     *     ],
     *     'showable' => boolean, // This control that this field will show or not in view page when view is enable for route
     *  ],
     * 
     */
    public function getColumns(): array
    {
        return array_merge(
            parent::getDefaultPreColumns(),
            array(
                'parent_id' => [
                    'name' => 'parent_id',
                    'labels' => [
                        'table' => 'Parent Category',
                        'export' => 'Parent Name'
                    ],
                    'thead' => [
                        'view' => 'buttons.sortit',
                        'value' => '',
                        'align' => '',
                    ],
                    'tbody' => [
                        'view' => 'cells.text-value',
                        'value' => 'getParentName',
                        'align' => '',
                    ],
                    'viewable' => [
                        'active' => false,
                        'trash' => false
                    ],
                    'expandable' => [
                        'active' => true,
                        'trash' => false,
    
                    ],
                    'sortable' => false,
                    'filterable' => [
                        'active' => true,
                        'trash' => false
                    ],
                    'importable' => true,
                    'exportable' => [
                        'active' => true,
                        'trash' => false,
                        'value' => 'getParentName'
                    ],
                    'artificial' => false,
                    'fillable' => [
                        'type' => '',
                        'style' => '',
                        'placeholder' => 'Choose Parent Category',
                        'component' => 'forms.select-option',
                        'options' =>  [
                            'data' => self::selectOptions('parent_id'),
                            'withRelation' => true,
                            'relation' => 'child',
                        ],
                        'attributes' => ['autofocus'],
                        'rows' => ''
                    ],
    
                ],
                'name' => [
                    'name' => 'name',
                    'labels' => [
                        'table' => 'Name',
                        'export' => 'Name'
                    ],
    
                    'thead' => [
                        'view' => 'buttons.sortit',
                        'value' => '',
                        'align' => '',
                    ],
                    'tbody' => [
                        'view' => 'cells.text-value',
                        'value' => 'getFamilyName',
                        'align' => '',
                    ],
                    'viewable' => [
                        'active' => true,
                        'trash' => true
                    ],
                    'expandable' => [
                        'active' => false,
                        'trash' => false
                    ],
                    'sortable' => true,
                    'filterable' => [
                        'active' => true,
                        'trash' => true
                    ],
                    'importable' => true,
                    'exportable' => [
                        'active' => true,
                        'trash' => true,
                        'value' => 'getValue'
                    ],
                    'artificial' => false,
                    'fillable' => [
                        'type' => 'text',
                        'style' => '',
                        'placeholder' => 'Name',
                        'component' => 'forms.input-box',
                        'attributes' => ['required'],
                        'rows' => ''
                    ],
    
    
                ],
            ),
            parent::getOptionalImageColumn(),
           // parent::getDefaultImagesColumn(),
            parent::getDefaultSlugColumns(),
            parent::getDefaultPostColumns(),
        );
    }

}