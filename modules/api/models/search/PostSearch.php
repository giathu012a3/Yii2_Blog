<?php

declare(strict_types=1);

namespace app\modules\api\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Post;
use app\models\PostLike;
use Yii;

/**
 * PostSearch represents the model behind the search form of `app\models\Post`.
 */
class PostSearch extends Post
{
    public $tag;
    public $tag_id;
    public $isManagement = false;

    /**
     * Override behaviors to avoid running SluggableBehavior and generating unique slug checks during search.
     */
    public function behaviors(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'status', 'category_id', 'author_id', 'published_at', 'created_at', 'updated_at', 'tag_id'], 'integer'],
            [['title', 'content', 'slug', 'tag'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     * @param string|null $formName
     * @return ActiveDataProvider
     */
    public function search(array $params, ?string $formName = '')
    {
        $userId  = Yii::$app->user->id;
        $isAdmin = Yii::$app->user->can('admin');

        $query = Post::find()->active();
        $query->select([
            'post.*',
            'like_count' => PostLike::find()
                ->select('COUNT(*)')
                ->where('post_like.post_id = post.id')
        ]);

        if ($this->isManagement) {
            if (!$isAdmin) {
                $query->andWhere(['post.author_id' => $userId]);
            }
        } else {
            $query->andWhere(['post.status' => Post::STATUS_PUBLISHED]);
        }

        $expand = array_filter(explode(',', Yii::$app->request->get('expand', '')));
        $validExpands = ['category', 'tags', 'author', 'thumbnail'];
        $withRelations = array_intersect($expand, $validExpands);

        // Always eager load the thumbnail relation to prevent N+1 queries for thumbnail_url
        if (!in_array('thumbnail', $withRelations, true)) {
            $withRelations[] = 'thumbnail';
        }

        if (!empty($withRelations)) {
            $query->with($withRelations);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeParam'   => 'limit',
                'defaultPageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'title',
                    'status',
                    'view_count',
                    'published_at',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'post.id'           => $this->id,
            'post.status'       => $this->status,
            'post.category_id'  => $this->category_id,
            'post.author_id'    => $this->author_id,
            'post.published_at' => $this->published_at,
            'post.created_at'   => $this->created_at,
            'post.updated_at'   => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'post.title', $this->title])
              ->andFilterWhere(['like', 'post.slug', $this->slug]);

        if (!empty($this->tag)) {
            $query->joinWith('tags')
                  ->andWhere(['like', 'tag.name', $this->tag]);
        }
        if (!empty($this->tag_id)) {
            $query->joinWith('postTags')
                  ->andWhere(['post_tag.tag_id' => $this->tag_id]);
        }

        return $dataProvider;
    }
}
