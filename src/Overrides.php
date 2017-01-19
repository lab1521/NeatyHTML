<?php
namespace Lab1521\NeatyHTML;

trait Overrides
{
    /**
     * Custom tag overrides to allow blocked tags to display.
     *
     * @param array $overrides Collection of tag overrides
     *
     * @return array
     */
    public function tagOverrides($overrides = [])
    {
        return Config::settings('tagOverrides', $overrides);
    }

    /**
     * Blocked tags to delete from document.
     *
     * @param array $tags Collection of tags
     *
     * @return array
     */
    public function blockedTags($tags = [])
    {
        return Config::settings('tags', $tags);
    }

    /**
     * Blocked tag attributes to delete from document.
     *
     * @param array $attributes Collection of tag attributes
     *
     * @return array
     */
    public function blockedAttributes($attributes = [])
    {
        return Config::settings('attr', $attributes);
    }
}
