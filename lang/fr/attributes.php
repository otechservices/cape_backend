<?php

/*
|--------------------------------------------------------------------------
| Libellés français des champs validés
|--------------------------------------------------------------------------
|
| Ce fichier centralise la traduction des noms de champs utilisés par les
| FormRequests. Il alimente la clé "attributes" de lang/fr/validation.php.
| Pour ajouter un nouveau champ, il suffit de l'ajouter ici : tous les
| messages de validation le concernant seront automatiquement en français.
|
*/

return [

    // --- Identité / utilisateur ---
    'firstname'                  => 'prénom',
    'lastname'                   => 'nom',
    'name'                       => 'nom',
    'name_chief'                 => 'nom du responsable',
    'chief_name'                 => 'nom du responsable',
    'email'                      => 'email',
    'phone'                      => 'numéro de téléphone',
    'sex'                        => 'sexe',
    'birthdate'                  => 'date de naissance',
    'birthplace'                 => 'lieu de naissance',
    'address'                    => 'adresse',
    'job'                        => 'profession',
    'identite'                   => 'identité',
    'user_id'                    => 'utilisateur',
    'causer_id'                  => 'utilisateur à l\'origine de l\'action',
    'causer_type'                => 'type d\'auteur',
    'user_up'                    => 'utilisateur du dessus',
    'user_down'                  => 'utilisateur du dessous',

    // --- Authentification / sécurité ---
    'password'                   => 'mot de passe',
    'password_confirmation'      => 'confirmation du mot de passe',
    'old_password'               => 'ancien mot de passe',
    'new_password'               => 'nouveau mot de passe',
    'new_password_confirmation'  => 'confirmation du nouveau mot de passe',
    'token'                      => 'jeton',
    'push_token'                 => 'jeton de notification',
    'verification_code'          => 'code de vérification',
    'code'                       => 'code',
    'use_2FA'                    => 'double authentification',
    'mode_2FA'                   => 'mode de double authentification',
    'for_login'                  => 'usage à la connexion',
    'role'                       => 'rôle',
    'permissions'                => 'permissions',
    'label_names'                => 'libellés',
    'guard_name'                 => 'garde',
    'group_name'                 => 'groupe',
    'key'                        => 'clé',
    'value'                      => 'valeur',

    // --- Structures / localisation ---
    'acronym'                    => 'sigle',
    'municipality_id'            => 'commune',
    'department_id'              => 'département',
    'district_id'                => 'arrondissement',
    'centre_id'                  => 'centre',
    'cps_id'                     => 'CPS',
    'cape_id'                    => 'CAPE',
    'service_id'                 => 'service',
    'project_id'                 => 'projet',

    // --- Contenus / publications ---
    'title'                      => 'titre',
    'subtitle'                   => 'sous-titre',
    'object'                     => 'objet',
    'subject_id'                 => 'sujet',
    'subject_type'               => 'type de sujet',
    'content'                    => 'contenu',
    'resume'                     => 'résumé',
    'description'                => 'description',
    'author'                     => 'auteur',
    'big_photo'                  => 'grande image',
    'short_photo'                => 'vignette',
    'filename'                   => 'fichier',
    'size'                       => 'taille',
    'weight'                     => 'poids',
    'type'                       => 'type',
    'data'                       => 'données',
    'properties'                 => 'propriétés',
    'note'                       => 'note',
    'note_max'                   => 'note maximale',
    'observation'                => 'observation',
    'instruction'                => 'instruction',
    'decision'                   => 'décision',
    'avis'                       => 'avis',
    'sens'                       => 'sens de l\'avis',
    'treatment'                  => 'traitement',
    'status'                     => 'statut',
    'priorite'                   => 'priorité',
    'canal'                      => 'canal',
    'delay'                      => 'délai',
    'durer'                      => 'durée',
    'dure_deuiduite'             => 'durée de conduite',
    'seance_interactive'         => 'séance interactive',
    'invite_date'                => 'date d\'invitation',
    'date_control'               => 'date du contrôle',
    'members'                    => 'membres',
    'session_member_id'          => 'membre de session',
    'requete_id'                 => 'requête',
    'billing_id'                 => 'facture',
    'type_billing_id'            => 'type de facture',
    'type_control_id'            => 'type de contrôle',
    'activity_report_id'         => 'rapport d\'activité',
    'activity_report_filename'   => 'fichier du rapport d\'activité',
    'financial_report_filename'  => 'fichier du rapport financier',

    // --- Journalisation / notifications ---
    'log_name'                   => 'journal',
    'event'                      => 'évènement',
    'batch_uuid'                 => 'identifiant du lot',
    'notifiable_id'              => 'destinataire',
    'notifiable_type'            => 'type de destinataire',
    'notification_list'          => 'liste des notifications',
    'notification_list.*'        => 'notification',
    'accept_notification'        => 'acceptation des notifications',
    'read_at'                    => 'date de lecture',

    // --- Drapeaux / états ---
    'is_active'                  => 'statut d\'activation',
    'is_valid'                   => 'validité',
    'is_transmitted'             => 'transmission',
    'isLast'                     => 'dernier élément',
    'show_only'                  => 'affichage seul',
    'show_edit'                  => 'affichage en modification',

];
