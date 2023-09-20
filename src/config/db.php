<?php

return [
    'dsn' => "sqlite:{$_SERVER['DOCUMENT_ROOT']}/links.db",
    'user' => null,
    'passw' => null,
    'attr' => [\PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
];