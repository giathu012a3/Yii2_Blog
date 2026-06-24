<?php

namespace app\modules\api\models\forms;

use yii\base\Model;

class AiForm extends Model
{
    public $description;
    public $content;
    public $text;

    const SCENARIO_GENERATE_TITLE = 'generate-title';
    const SCENARIO_GENERATE_SUMMARY = 'generate-summary';
    const SCENARIO_IMPROVE_TEXT = 'improve-text';

    public function scenarios()
    {
        return [
            self::SCENARIO_GENERATE_TITLE => ['description'],
            self::SCENARIO_GENERATE_SUMMARY => ['content'],
            self::SCENARIO_IMPROVE_TEXT => ['text'],
        ];
    }

    public function rules()
    {
        return [
            [['description'], 'required', 'on' => self::SCENARIO_GENERATE_TITLE],
            [['description'], 'string', 'on' => self::SCENARIO_GENERATE_TITLE],

            [['content'], 'required', 'on' => self::SCENARIO_GENERATE_SUMMARY],
            [['content'], 'string', 'on' => self::SCENARIO_GENERATE_SUMMARY],

            [['text'], 'required', 'on' => self::SCENARIO_IMPROVE_TEXT],
            [['text'], 'string', 'on' => self::SCENARIO_IMPROVE_TEXT],
        ];
    }
}
