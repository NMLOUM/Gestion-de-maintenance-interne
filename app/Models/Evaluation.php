<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec le ticket
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relation avec l'utilisateur qui a évalué
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour obtenir les évaluations d'un ticket
     */
    public function scopeForTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    /**
     * Scope pour obtenir la moyenne des évaluations
     */
    public function scopeAverageRating($query)
    {
        return $query->avg('rating');
    }

    /**
     * Vérifier si une évaluation est positive (4-5 étoiles)
     */
    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    /**
     * Vérifier si une évaluation est négative (1-2 étoiles)
     */
    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }

    /**
     * Obtenir le label de la note
     */
    public function getRatingLabelAttribute(): string
    {
        return match($this->rating) {
            1 => 'Très insatisfait',
            2 => 'Insatisfait',
            3 => 'Neutre',
            4 => 'Satisfait',
            5 => 'Très satisfait',
            default => 'Non évalué',
        };
    }
}
