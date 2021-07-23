<?php

namespace Log1x\Poet\Modules;

use Illuminate\Support\Str;

class BlockCategoryModule extends AbstractModule
{
    /**
     * The module key.
     *
     * @var string
     */
    protected $key = 'block_category';

    /**
     * Handle the module.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->config->isEmpty()) {
            return;
        }

        // check for the new filter introduced in WordPress 5.8
        $hookName = has_filter('block_categories_all') ? 'block_categories_all' : 'block_categories';

        add_filter($hookName, function ($categories) {
            $categories = $this->collect($categories)->keyBy('slug');

            return $this->config->map(function ($value, $key) use ($categories) {
                if (empty($key) || is_int($key)) {
                    $key = $value;
                }

                if ($categories->has($key)) {
                    if ($value === false) {
                        return $categories->forget($key);
                    }

                    if (is_string($value)) {
                        $value = ['title' => Str::title($value)];
                    }

                    return $categories->put(
                        $key,
                        array_merge($categories->get($key), $value)
                    );
                }

                if (! is_array($value)) {
                    return [
                        'slug' => Str::slug($key),
                        'title' => Str::title($value ?? $key),
                        'icon' => null,
                    ];
                }

                return array_merge([
                    'slug' => Str::slug($key),
                    'title' => Str::title($key),
                    'icon' => null,
                ], $value ?? []);
            })
            ->merge($categories->all())
            ->filter()
            ->sort()
            ->values()
            ->all();
        });
    }
}
