<?php

namespace App\Http\Repositories;

use App\Traits\Repository;
 use App\Models\Message;
use App\Models\Cape;
use App\Utilities\FileStorage;

use Auth;

class MessageRepository
{
    use Repository;

    /**
     * Le modèle utilisé.
     *
     * @var Message
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->model = app(Message::class);
    }

    /**
     * Vérifie si la fête existe.
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Récupère toutes les fêtes avec pagination et filtres.
     */
    public function getAll($request)
    {
        $per_page = 10;

        // Construire la requête avec filtres et tri
        $req = Message::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        // Pagination ou récupération brute
        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request->per_page;
            $messages = $req->paginate($per_page);
        } else {
            $messages = $req->get();
        }

        return $messages;
    }

    /**
     * Récupère une fête spécifique.
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Crée une nouvelle fête.
     */
    public function makeStore(Request $request): Message
    {
        $datas = $request->all();
        $message = new Message($datas);
        $message->save();

        return $message;
    }

    /**
     * Met à jour une fête.
     */
    public function makeUpdate(Request $request, $id): Message
    {
        $datas = $request->all();

        $message = Message::findOrFail($id);
        $message->update($datas);

        return $message;
    }

    /**
     * Supprime une fête.
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Récupère les fêtes les plus récentes.
     */
    public function getlatest()
    {
        return $this->latest()->get();
    }

    public function show($id)
    {
        $message = Message::find($id);
        return $message;
    }

}
