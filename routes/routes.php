<?php

use Slim\App;
use App\Controllers\CAppRequest;
use App\Controllers\CAdmin;
use App\Controllers\CLogin;
use App\Controllers\CReferences;
use App\Controllers\CResults;

use App\Controllers\CAuthMiddleware;

return function (App $app) {
    $container = $app->getContainer();
    /**
     * CAuthMiddleware  middlware проверка токена (Auth: Bearer **token**)
     * при POST login выдается новый токен на определенное время
     *
     * при запросе в в рамках этого времени счетчик врмени обнуляется
     *
     * иначе POST login
     */
    
    /**
     * Login Basic auth для получения токена
     */
    $app->post('/api/login', CLogin::class);
    
    /**
     * Група API получения результатов
     */
    $app->group('/api/results', function () {
    
        /**
         * Выдаются готовые результаты исследований начиная с даты/времени валидации
         * полученных при последнем обращении.
         * Если обращение клиента происходит первый раз, то выдаются готовые результаты
         * начиная с 00:00:00 текущей даты
         */
        $this->get('/targetscomplete', CResults::class . ':getResultsByTargetsComplete');
    
        /**
         * Выдаются готовые результаты исследований по штрихкоду пробы
         *
         * @bcode   1234567890 (int)
         */
        $this->get('/barcode/{bcode}', CResults::class . ':getResultsByBarcode');
    
        /**
         * Выдаются готовые результаты исследований по номкру амбулатоорной карты (ЭМК)
         * и дате регистрации заявки
         *
         * @acard   номер амбулаторной карты (vchar(32))
         * @dreg    дата регистрации заявки (string '2019-06-05')
         */
        $this->get('/ambulcard/{acard}/{dreg}', CResults::class . ':getResultsByAmbulcardDate');
    
        /**
         * Получение дополнительного файла описания результатов исследования в формате PDF
         *
         * @id      ID файла полученного из ответа на заявку (int)
         */
        $this->get('/attachfile/{id}', CResults::class . ':getAttachFile');
        
    })->add(new CAuthMiddleware($container));
    
    /**
     * Група API получения справочников для МИС
     */
    $app->group('/api/reference', function () {
    
        /**
         * Справочник биоматериалов в JSON
         *
         * {code, name}
         */
        $this->get('/biomaterials', CReferences::class . ':getBiomaterials');
    
        /**
         * Справочник исследований в JSON
         *
         * {code, name}
         */
        $this->get('/targets', CReferences::class . ':getTargets');
    
        /**
         * Справочник соотношений исследований к биоматериалам в JSON
         *
         * {codeBiomaterial, nameBiomaterial, codeTarget, nameTarget}
         */
        $this->get('/tbrelations', CReferences::class . ':getTbrelations');
        
    })->add(new CAuthMiddleware($container));
    
    /**
     * Регистрация в ЛИС новой заявки
     *
     * передается заявка в формате JSON в теле запроса
     */
    $app->put('/api/request', CAppRequest::class . ':addNewAppRequest')->add(new CAuthMiddleware($container));
    
    /**
     * администрирование
     */
    $app->group('/api/admin', function () {
    
        /**
         * создание нового пользователя API
         * либо смена пароля или доступа
         *
         * @hcode       код клиента (vchar(32))
         * @login       логин (vchar(64))
         * @password    пароль (vchsr(32))
         * @admin       права 0/1 (по умолчанию 0)
         */
        $this->post('/newuser', CAdmin::class . ':addNewUser');
        
    })->add(new CAuthMiddleware($container));
};
