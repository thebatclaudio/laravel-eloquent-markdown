<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use TheBatClaudio\EloquentMarkdown\Models\MarkdownModel;

class TestModel extends MarkdownModel
{
}

beforeEach(function () {
    Config::set('markdown-model.path', __DIR__.'/../content/pages');
});

it('returns the right file calling find method', function () {
    $fileId = 'test';

    $markdown = TestModel::find('test');

    expect($markdown)
        ->toBeInstanceOf(TestModel::class)
        ->not->toBeEmpty()
        ->and($markdown?->toArray())
        ->toHaveKeys([
            // YAML Front Matter attributes
            'first_attribute',
            'second_attribute',
            'third_attribute',
            // Content
            'content',
            // Default keys: file_path, file_name, id
            'file_path',
            'file_name',
            'id',
        ])
        ->and($markdown?->first_attribute)
        ->toBe('First attribute')
        ->and($markdown?->second_attribute)
        ->toBe('Second attribute')
        ->and($markdown?->third_attribute)
        ->toBe('Third attribute')
        ->and($markdown?->content)
        ->toContain('The time has come')
        ->and($markdown?->file_path)
        ->toBe(Config::get('markdown-model.path').'/'.$fileId.MarkdownModel::FILE_EXTENSION)
        ->and($markdown?->file_name)
        ->toBe($fileId.MarkdownModel::FILE_EXTENSION)
        ->and($markdown?->id)
        ->toBe($fileId);
});

it('returns all files calling all method', function () {
    $markdowns = TestModel::all()->toArray();

    expect($markdowns)
        ->not->toBeEmpty()
        ->toHaveCount(4)
        ->and(array_keys($markdowns))
        ->toContain(
            'test',
            'test2',
            'test3',
            'folder/test'
        );
});

it('should save file', function () {
    $fileId = 'create-test';

    $markdown = new TestModel();

    $markdown->id = $fileId;
    $markdown->attribute = 'test';

    $markdown->content = 'content';

    $markdown->save();

    $filepath = Config::get('markdown-model.path').'/'.$fileId.MarkdownModel::FILE_EXTENSION;

    expect(file_exists($filepath))
        ->toBeTrue();

    unlink($filepath);
});

it('should delete file', function () {
    $fileId = 'delete-test';

    $markdown = new TestModel();

    $markdown->id = $fileId;
    $markdown->attribute = 'test';

    $markdown->content = 'content';

    $markdown->save();

    $filepath = Config::get('markdown-model.path').'/'.$fileId.MarkdownModel::FILE_EXTENSION;

    expect(file_exists($filepath))
        ->toBeTrue();

    $markdown->delete();

    expect(file_exists($filepath))
        ->toBeFalse();
});

it('should update file', function () {
    $fileId = 'update-test';
    $filepath = Config::get('markdown-model.path').'/'.$fileId.MarkdownModel::FILE_EXTENSION;

    $markdown = new TestModel();

    $markdown->id = $fileId;
    $markdown->attribute = 'test';

    $markdown->content = 'content';

    $markdown->save();

    expect(file_exists($filepath))
        ->toBeTrue();

    $markdown->update([
        'attribute' => 'changed',
    ]);

    $markdown = TestModel::find($fileId);

    expect($markdown)
        ->not->toBeNull()
        ->and($markdown->attribute)
        ->toBe('changed');

    $markdown->delete();
});

it('should not find non existing file', function () {
    $fileId = 'not-found-test';

    $markdown = TestModel::find($fileId);

    expect($markdown)
        ->toBeNull();
});
