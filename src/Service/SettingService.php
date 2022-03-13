<?php

namespace App\Service;

use App\Repository\AppSettingsRepository;

class SettingService
{
    private AppSettingsRepository $appSettingsRepository;

    public function __construct(AppSettingsRepository $appSettingsRepository){

        $this->appSettingsRepository = $appSettingsRepository;
    }

    /**
     * @return bool|null
     *
     * Describes what auth method the employers should use
     */
    public function authByPin(): ?bool
    {
        return $this->appSettingsRepository->findOneBy(['id'=>1])->getAuthOnlyByPin();
    }

}