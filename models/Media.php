<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\MediaBase;
use app\models\query\MediaQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
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
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ],
        ];
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if ($this->isNewRecord && $this->_uploadedFile !== null) {
            $this->mime_type = $this->_uploadedFile->type;
            $this->size = $this->_uploadedFile->size;

            $fileName = Yii::$app->security->generateRandomString(32) . '.' . $this->_uploadedFile->extension;
            $fileUrl = Yii::$app->r2->upload($this->_uploadedFile->tempName, $fileName, $this->_uploadedFile->type);

            if ($fileUrl === null) {
                $this->addError('uploadedFile', 'Failed to upload file to Cloudflare R2.');
                return false;
            }

            $this->file_name = $fileName;
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
