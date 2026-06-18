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
        if ($this->user_id === null && Yii::$app->has('user') && !Yii::$app->user->isGuest) {
            $this->user_id = (int)Yii::$app->user->id;
        }

        if ($this->isNewRecord) {
            if ($this->_uploadedFile === null) {
                $this->addError('uploadedFile', 'Uploaded file is required.');
                return false;
            }
            $this->mime_type = $this->_uploadedFile->type;
            $this->size = $this->_uploadedFile->size;
        }

        return parent::beforeValidate();
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && $this->_uploadedFile !== null) {
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

    public function afterDelete(): void
    {
        parent::afterDelete();
        if (!empty($this->file_name)) {
            try {
                Yii::$app->r2->delete($this->file_name);
            } catch (\Throwable $e) {
                Yii::error("Failed to delete R2 file: " . $e->getMessage(), __METHOD__);
            }
        }
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

    public function fields(): array
    {
        return [
            'id',
            'user_id',
            'file_name',
            'file_url',
            'mime_type',
            'size',
            'created_at' => function () {
                return $this->created_at ? Yii::$app->formatter->asDatetime($this->created_at) : null;
            },
            'presigned_url' => function () {
                return $this->getPresignedUrl();
            },
        ];
    }

    public function getPresignedUrl(string $expires = '+20 minutes'): ?string
    {
        return Yii::$app->r2->getPresignedUrl($this->file_name, $expires);
    }
}

