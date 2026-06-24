<?php

namespace app\helpers;


class CacheHelper
{
    public static function getPostViewKey($id, array $params): string
    {
        if (!empty($params['expand']) && is_string($params['expand'])) {
            $expand = explode(',', $params['expand']);
            $expand = array_map('trim', $expand);
            $expand = array_values(array_filter($expand));

            sort($expand);

            $params['expand'] = implode(',', $expand);
        }

        ksort($params);

        return 'post_view_' . $id . '_' . md5(
            json_encode($params, JSON_UNESCAPED_UNICODE)
        );
    }

    public static function getCategoryListKey($params)
    {
        return 'catgory-list-' . md5(json_encode($params));
    }

    public static function getTagListKey($params)
    {
        return 'tag-list-' . md5(json_encode($params));
    }

    public static function getCategory()
    {
        return 'category';
    }

    public static function getTag()
    {
        return 'tag';
    }

    public static function getPostId($id)
    {
        return 'post-' . $id;
    }
}
