<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lignes de langue pour la validation
    |--------------------------------------------------------------------------
    |
    | Messages d'erreur par défaut retournés par le validateur. Toutes les
    | FormRequests du projet s'appuient sur ce fichier : il n'est donc plus
    | nécessaire de redéfinir un message dans chaque requête.
    |
    */

    'accepted'             => 'Le champ :attribute doit être accepté.',
    'accepted_if'          => 'Le champ :attribute doit être accepté quand :other vaut :value.',
    'active_url'           => 'Le champ :attribute n\'est pas une URL valide.',
    'after'                => 'Le champ :attribute doit être une date postérieure au :date.',
    'after_or_equal'       => 'Le champ :attribute doit être une date postérieure ou égale au :date.',
    'alpha'                => 'Le champ :attribute ne doit contenir que des lettres.',
    'alpha_dash'           => 'Le champ :attribute ne doit contenir que des lettres, des chiffres, des tirets et des underscores.',
    'alpha_num'            => 'Le champ :attribute ne doit contenir que des lettres et des chiffres.',
    'array'                => 'Le champ :attribute doit être une liste.',
    'ascii'                => 'Le champ :attribute ne doit contenir que des caractères alphanumériques et des symboles simples.',
    'before'               => 'Le champ :attribute doit être une date antérieure au :date.',
    'before_or_equal'      => 'Le champ :attribute doit être une date antérieure ou égale au :date.',
    'between'              => [
        'array'   => 'Le champ :attribute doit contenir entre :min et :max éléments.',
        'file'    => 'Le fichier :attribute doit avoir une taille comprise entre :min et :max kilo-octets.',
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'string'  => 'Le champ :attribute doit contenir entre :min et :max caractères.',
    ],
    'boolean'              => 'Le champ :attribute doit être vrai ou faux.',
    'can'                  => 'Le champ :attribute contient une valeur non autorisée.',
    'confirmed'            => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password'     => 'Le mot de passe est incorrect.',
    'date'                 => 'Le champ :attribute n\'est pas une date valide.',
    'date_equals'          => 'Le champ :attribute doit être une date égale au :date.',
    'date_format'          => 'Le champ :attribute ne correspond pas au format :format.',
    'decimal'              => 'Le champ :attribute doit comporter :decimal décimales.',
    'declined'             => 'Le champ :attribute doit être refusé.',
    'declined_if'          => 'Le champ :attribute doit être refusé quand :other vaut :value.',
    'different'            => 'Les champs :attribute et :other doivent être différents.',
    'digits'               => 'Le champ :attribute doit contenir :digits chiffres.',
    'digits_between'       => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'dimensions'           => 'La taille de l\'image :attribute n\'est pas conforme.',
    'distinct'             => 'Le champ :attribute contient une valeur en double.',
    'doesnt_end_with'      => 'Le champ :attribute ne doit pas se terminer par l\'un des éléments suivants : :values.',
    'doesnt_start_with'    => 'Le champ :attribute ne doit pas commencer par l\'un des éléments suivants : :values.',
    'email'                => 'Le champ :attribute doit être une adresse email valide.',
    'ends_with'            => 'Le champ :attribute doit se terminer par l\'un des éléments suivants : :values.',
    'enum'                 => 'La valeur sélectionnée pour :attribute est invalide.',
    'exists'               => 'La valeur sélectionnée pour :attribute n\'existe pas.',
    'file'                 => 'Le champ :attribute doit être un fichier.',
    'filled'               => 'Le champ :attribute doit être renseigné.',
    'gt'                   => [
        'array'   => 'Le champ :attribute doit contenir plus de :value éléments.',
        'file'    => 'Le fichier :attribute doit être plus grand que :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'string'  => 'Le champ :attribute doit contenir plus de :value caractères.',
    ],
    'gte'                  => [
        'array'   => 'Le champ :attribute doit contenir au moins :value éléments.',
        'file'    => 'Le fichier :attribute doit être plus grand ou égal à :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'string'  => 'Le champ :attribute doit contenir au moins :value caractères.',
    ],
    'image'                => 'Le champ :attribute doit être une image.',
    'in'                   => 'La valeur sélectionnée pour :attribute est invalide.',
    'in_array'             => 'Le champ :attribute n\'existe pas dans :other.',
    'integer'              => 'Le champ :attribute doit être un nombre entier.',
    'ip'                   => 'Le champ :attribute doit être une adresse IP valide.',
    'ipv4'                 => 'Le champ :attribute doit être une adresse IPv4 valide.',
    'ipv6'                 => 'Le champ :attribute doit être une adresse IPv6 valide.',
    'json'                 => 'Le champ :attribute doit être un document JSON valide.',
    'lowercase'            => 'Le champ :attribute doit être en minuscules.',
    'lt'                   => [
        'array'   => 'Le champ :attribute doit contenir moins de :value éléments.',
        'file'    => 'Le fichier :attribute doit être plus petit que :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'string'  => 'Le champ :attribute doit contenir moins de :value caractères.',
    ],
    'lte'                  => [
        'array'   => 'Le champ :attribute ne doit pas contenir plus de :value éléments.',
        'file'    => 'Le fichier :attribute doit être plus petit ou égal à :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'string'  => 'Le champ :attribute doit contenir au plus :value caractères.',
    ],
    'mac_address'          => 'Le champ :attribute doit être une adresse MAC valide.',
    'max'                  => [
        'array'   => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'file'    => 'Le fichier :attribute ne doit pas dépasser :max kilo-octets.',
        'numeric' => 'Le champ :attribute ne doit pas être supérieur à :max.',
        'string'  => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'max_digits'           => 'Le champ :attribute ne doit pas contenir plus de :max chiffres.',
    'mimes'                => 'Le champ :attribute doit être un fichier de type : :values.',
    'mimetypes'            => 'Le champ :attribute doit être un fichier de type : :values.',
    'min'                  => [
        'array'   => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file'    => 'Le fichier :attribute doit faire au moins :min kilo-octets.',
        'numeric' => 'Le champ :attribute doit être au moins égal à :min.',
        'string'  => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'min_digits'           => 'Le champ :attribute doit contenir au moins :min chiffres.',
    'missing'              => 'Le champ :attribute doit être absent.',
    'missing_if'           => 'Le champ :attribute doit être absent quand :other vaut :value.',
    'missing_unless'       => 'Le champ :attribute doit être absent sauf si :other vaut :value.',
    'missing_with'         => 'Le champ :attribute doit être absent lorsque :values est présent.',
    'missing_with_all'     => 'Le champ :attribute doit être absent lorsque :values sont présents.',
    'multiple_of'          => 'Le champ :attribute doit être un multiple de :value.',
    'not_in'               => 'La valeur sélectionnée pour :attribute est invalide.',
    'not_regex'            => 'Le format du champ :attribute est invalide.',
    'numeric'              => 'Le champ :attribute doit être un nombre.',
    'password'             => [
        'letters'       => 'Le champ :attribute doit contenir au moins une lettre.',
        'mixed'         => 'Le champ :attribute doit contenir au moins une majuscule et une minuscule.',
        'numbers'       => 'Le champ :attribute doit contenir au moins un chiffre.',
        'symbols'       => 'Le champ :attribute doit contenir au moins un caractère spécial.',
        'uncompromised' => 'Le champ :attribute apparaît dans une fuite de données. Veuillez en choisir un autre.',
    ],
    'present'              => 'Le champ :attribute doit être présent.',
    'prohibited'           => 'Le champ :attribute est interdit.',
    'prohibited_if'        => 'Le champ :attribute est interdit quand :other vaut :value.',
    'prohibited_unless'    => 'Le champ :attribute est interdit sauf si :other est dans :values.',
    'prohibits'            => 'Le champ :attribute interdit la présence de :other.',
    'regex'                => 'Le format du champ :attribute est invalide.',
    'required'             => 'Le champ :attribute est obligatoire.',
    'required_array_keys'  => 'Le champ :attribute doit contenir les entrées suivantes : :values.',
    'required_if'          => 'Le champ :attribute est obligatoire quand :other vaut :value.',
    'required_if_accepted' => 'Le champ :attribute est obligatoire quand :other est accepté.',
    'required_unless'      => 'Le champ :attribute est obligatoire sauf si :other est dans :values.',
    'required_with'        => 'Le champ :attribute est obligatoire lorsque :values est présent.',
    'required_with_all'    => 'Le champ :attribute est obligatoire lorsque :values sont présents.',
    'required_without'     => 'Le champ :attribute est obligatoire lorsque :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est obligatoire lorsque aucun de :values n\'est présent.',
    'same'                 => 'Les champs :attribute et :other doivent être identiques.',
    'size'                 => [
        'array'   => 'Le champ :attribute doit contenir :size éléments.',
        'file'    => 'Le fichier :attribute doit faire :size kilo-octets.',
        'numeric' => 'Le champ :attribute doit être égal à :size.',
        'string'  => 'Le champ :attribute doit contenir :size caractères.',
    ],
    'starts_with'          => 'Le champ :attribute doit commencer par l\'un des éléments suivants : :values.',
    'string'               => 'Le champ :attribute doit être une chaîne de caractères.',
    'timezone'             => 'Le champ :attribute doit être un fuseau horaire valide.',
    'unique'               => 'Cette valeur de :attribute est déjà utilisée.',
    'uploaded'             => 'Le téléversement du fichier :attribute a échoué.',
    'uppercase'            => 'Le champ :attribute doit être en majuscules.',
    'url'                  => 'Le champ :attribute doit être une URL valide.',
    'ulid'                 => 'Le champ :attribute doit être un ULID valide.',
    'uuid'                 => 'Le champ :attribute doit être un UUID valide.',

    /*
    |--------------------------------------------------------------------------
    | Messages de validation personnalisés
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'password' => [
            'min'       => 'Le mot de passe doit contenir au moins :min caractères.',
            'confirmed' => 'Les deux mots de passe saisis ne sont pas identiques.',
        ],
        'new_password' => [
            'min'       => 'Le nouveau mot de passe doit contenir au moins :min caractères.',
            'confirmed' => 'Les deux mots de passe saisis ne sont pas identiques.',
        ],
        'email' => [
            'exists' => 'Aucun compte n\'est associé à cette adresse email.',
            'unique' => 'Cette adresse email est déjà utilisée.',
        ],
        'phone' => [
            'unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'regex'  => 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.',
        ],
        'old_password' => [
            'required' => 'L\'ancien mot de passe est obligatoire.',
        ],
        'token' => [
            'exists' => 'Ce lien de réinitialisation est invalide ou a expiré.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Noms des champs en français
    |--------------------------------------------------------------------------
    |
    | Utilisés pour remplacer :attribute dans les messages ci-dessus.
    |
    */

    'attributes' => require __DIR__.'/attributes.php',

];
