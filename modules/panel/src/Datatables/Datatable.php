<?php

namespace Fpaipl\Panel\Datatables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Fluent;

interface Datatable
{
    /**
     * Here It provide the base query.
     */
    public static function baseQuery($model): Builder;

    /**
     * Here it provide the relational data for dropdown creation.
     */
    public function selectOptions($field): Collection;

    /**
     * Fluent class is a utility class provided by the Laravel framework that allows 
     * you to create and manipulate objects fluently. This means you can chain together 
     * method calls and property assignments in a more expressive and readable way 
     * without nesting long chains of method calls or using complex arrays
     */
    public function getfields(): Fluent;

    /**
     * Here we provide the top buttons which comes on list page Import, DownloadSample.
     */
    public function topButtonsPart1(): array;

    /**
     * Here we provide the top buttons which comes on list page Export, BulkDelete, Trash.
     */
    public function topButtonsPart2(): array;

    /**
     * Here we provide all the table related buttons which comes on list page like view, edit etc.
     */
    public function tableButtons(): array;

    /**
     * Here we provide all the button comes on list page.
     */
    public function buttons($position): array;

    /**
     * It controll the all features comes on list page like search, filter, bulk action, pagination etc
     */
    public function features(): array;

    /**
     * Here we provide all the table fields detail.
     */
    public function getColumns(): array;

    /**
     * Here we provide all those table fields detail that comes before specific model related table fields.
     */
    public function getDefaultPreColumns(): array;

    /**
     * Here we provide all those table fields detail that comes after specific model related table fields.
     */
    public function getDefaultPostColumns(): array;

    /**
    * Here we provide the slug column
    */

    public function getDefaultSlugColumns(): array;

    /**
     * Here we provide single image detail.
     */
    public function getDefaultImageColumn(): array;

    /**
     * Here we provide multiple images detail.
     */
    public function getDefaultImagesColumn(): array;

    /**
     * Here we provide all the page & validation messages.
     */
    public function getMessages(): array;

    /**
     * It is used for generating the filename of excel file in exporting the data.
     */
    public function filename(): string;

    /**
     * It gives the model name used for generating the unique id of input field.
     */
    public function getModelName($type): string;
}