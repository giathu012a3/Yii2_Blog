<?php

namespace app\modules\api\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Post;
use app\rbac\Permission;
use Yii;

/**
 * PostSearch represents the model behind the search form of `app\models\Post`.
 */
class PostSearch extends Post
{
    public $tag;

    private function getExpandableRelations(): array
    {
        return [
            'category' => [],
            'tags' => [],
            'comments' => ['author', 'replies', 'replies.author'],
        ];
    }

    public function behaviors()
    {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'author_id', 'status', 'published_at', 'view_count', 'is_deleted', 'deleted_at', 'created_at', 'updated_at'], 'integer'],
            [['title', 'description', 'slug', 'content', 'tag'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $expand = Yii::$app->request->get('expand');
        $with = ['author', 'thumbnailMedia'];

        if (is_string($expand) && !empty($expand)) {
            $requested = explode(',', $expand);

            $allowed = $this->getExpandableRelations();
            foreach ($requested as $relation) {
                $relation = trim($relation);
                if (!isset($allowed[$relation])) {
                    continue;
                }
                $with[] = $relation;
                foreach ($allowed[$relation] as $child) {
                    $with[] = "{$relation}.{$child}";
                }
            }

            $with = array_unique($with);
        }
        $query = Post::find()->with($with);


        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'post.id' => $this->id,
            'post.category_id' => $this->category_id,
            'post.author_id' => $this->author_id,
            'post.status' => $this->status,
            'post.published_at' => $this->published_at,
        ]);

        $query->andFilterWhere(['like', 'post.title', $this->title])
            ->andFilterWhere(['like', 'post.description', $this->description])
            ->andFilterWhere(['like', 'post.slug', $this->slug])
            ->andFilterWhere(['like', 'post.content', $this->content]);

        if (!empty($this->tag)) {
            $query->joinWith('tags')
                ->andWhere([
                    'or',
                    ['tag.name' => $this->tag],
                    ['tag.slug' => $this->tag]
                ]);
        }

        $isGuest = Yii::$app->user->isGuest;
        $isAuthor = !$isGuest && Yii::$app->user->can(Permission::AUTHOR_ACCESS);
        $isAdmin = !$isGuest && Yii::$app->user->can(Permission::ADMIN_ACCESS);
        $isReader = !$isGuest && !$isAuthor && !$isAdmin;

        if ($isGuest || $isReader) {
            $query->published()->notDelete();
        } elseif ($isAuthor && !$isAdmin) {
            $query->publishedOrOwn(Yii::$app->user->id)->notDelete();
        }

        if ($isAdmin) {
            if ($this->is_deleted === null || $this->is_deleted === '') {
                $query->notDelete();
            } else {
                $query->andWhere(['post.is_deleted' => (int)$this->is_deleted]);
            }
        }

        return $dataProvider;
    }
}
