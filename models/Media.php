<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\MediaBase;
use app\models\query\MediaQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;

/**
 * Media model extending MediaBase.
 */
class Media extends MediaBase
{
    private ?UploadedFile $_uploadedFile = null;

    public function setUploadedFile(UploadedFile $file): void
    {
        $this->_uploadedFile = $file;
    }

    public function getUploadedFile(): ?UploadedFile
    {
        return $this->_uploadedFile;
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && $this->_uploadedFile instanceof UploadedFile) {
            $uniqueName = Yii::$app->security->generateRandomString(32) . '.' . $this->_uploadedFile->extension;
            $fileUrl = Yii::$app->r2->upload($this->_uploadedFile->tempName, $uniqueName, $this->_uploadedFile->type);

            if ($fileUrl === null) {
                return false;
            }

            $this->file_name = $uniqueName;
            $this->file_url = $fileUrl;
        }

        return true;
    }

    public static function find(): MediaQuery
    {
        return new MediaQuery(static::class);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[MediaLinks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMediaLinks()
    {
        return $this->hasMany(MediaLink::class, ['media_id' => 'id']);
    }
}
