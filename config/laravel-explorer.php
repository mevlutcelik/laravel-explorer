<?php

return [
    /*
     * Araca erişilecek URL yolu. (Örn: projem.com/explorer)
     */
    'path' => 'explorer',

    /*
     * Güvenlik: Aracın hangi ortamlarda çalışacağını belirler.
     * Canlı ortamda (production) veri sızıntısını önlemek için varsayılan olarak sadece 'local'dir.
     */
    'environments' => ['local'],

    /*
     * Güvenlik için eklenecek middleware'ler.
     */
    'middleware' => ['web'],
];