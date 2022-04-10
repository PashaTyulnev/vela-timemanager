<?php

namespace App\Service;

use App\Repository\CompanyAppSettingsRepository;

class SettingService
{
    private CompanyAppSettingsRepository $appSettingsRepository;

    public function __construct(CompanyAppSettingsRepository $appSettingsRepository){

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