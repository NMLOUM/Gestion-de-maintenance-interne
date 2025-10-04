<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'requester_id',
        'assigned_to',
        'service_id',
        'category_id',
        'status',
        'priority',
        'location',
        'estimated_hours',
        'actual_hours',
        'assigned_at',
        'started_at',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Auto-générer le numéro de ticket
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ticket) {
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
        });
    }

    // Relations
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class)->orderBy('created_at', 'desc');
    }

    public function histories()
    {
        return $this->hasMany(TicketHistory::class)->orderBy('created_at', 'desc');
    }

    public function evaluation()
    {
        return $this->hasOne(Evaluation::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    // Accessors
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
            'cancelled' => 'Annulé',
            default => 'Inconnu'
        };
    }

    public function getPriorityDisplayAttribute()
    {
        return match($this->priority) {
            'low' => 'Faible',
            'normal' => 'Normal',
            'high' => 'Élevé',
            'critical' => 'Critique',
            default => 'Normal'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'low' => 'green',
            'normal' => 'blue',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'in_progress' => 'blue',
            'resolved' => 'green',
            'closed' => 'gray',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    public function getIsOverdueAttribute()
    {
        if ($this->status === 'closed' || $this->status === 'cancelled') {
            return false;
        }

        $daysSinceCreation = $this->created_at->diffInDays(now());

        return match($this->priority) {
            'critical' => $daysSinceCreation > 1,
            'high' => $daysSinceCreation > 3,
            'normal' => $daysSinceCreation > 7,
            'low' => $daysSinceCreation > 14,
            default => false
        };
    }

    /**
     * Obtenir le délai SLA en heures selon la priorité
     */
    public function getSlaHoursAttribute()
    {
        return match($this->priority) {
            'critical' => 24,  // 1 jour
            'high' => 72,      // 3 jours
            'normal' => 168,   // 7 jours
            'low' => 336,      // 14 jours
            default => 168
        };
    }

    /**
     * Obtenir le temps restant avant dépassement SLA (en heures)
     */
    public function getSlaRemainingHoursAttribute()
    {
        if ($this->status === 'closed' || $this->status === 'cancelled') {
            return null;
        }

        $hoursSinceCreation = $this->created_at->diffInHours(now());
        $remaining = $this->sla_hours - $hoursSinceCreation;

        return $remaining;
    }

    /**
     * Obtenir le pourcentage de temps SLA écoulé
     */
    public function getSlaPercentageAttribute()
    {
        if ($this->status === 'closed' || $this->status === 'cancelled') {
            return 0;
        }

        $hoursSinceCreation = $this->created_at->diffInHours(now());
        $percentage = ($hoursSinceCreation / $this->sla_hours) * 100;

        return min(100, round($percentage));
    }

    /**
     * Obtenir le statut SLA formaté pour l'affichage
     */
    public function getSlaStatusAttribute()
    {
        if ($this->status === 'closed' || $this->status === 'cancelled') {
            return [
                'status' => 'completed',
                'color' => 'gray',
                'label' => 'Terminé',
                'remaining' => null
            ];
        }

        $remaining = $this->sla_remaining_hours;

        if ($remaining < 0) {
            $overdue = abs($remaining);
            return [
                'status' => 'overdue',
                'color' => 'red',
                'label' => 'Dépassé',
                'remaining' => -$remaining,
                'formatted' => $this->formatHours($overdue) . ' de retard'
            ];
        }

        if ($remaining <= 4) {
            return [
                'status' => 'critical',
                'color' => 'red',
                'label' => 'Critique',
                'remaining' => $remaining,
                'formatted' => $this->formatHours($remaining) . ' restantes'
            ];
        }

        if ($remaining <= 24) {
            return [
                'status' => 'warning',
                'color' => 'orange',
                'label' => 'Urgent',
                'remaining' => $remaining,
                'formatted' => $this->formatHours($remaining) . ' restantes'
            ];
        }

        return [
            'status' => 'ok',
            'color' => 'green',
            'label' => 'Dans les temps',
            'remaining' => $remaining,
            'formatted' => $this->formatHours($remaining) . ' restantes'
        ];
    }

    /**
     * Formater les heures en texte lisible
     */
    private function formatHours($hours)
    {
        if ($hours < 1) {
            return round($hours * 60) . ' minutes';
        }

        if ($hours < 24) {
            return round($hours) . 'h';
        }

        $days = floor($hours / 24);
        $remainingHours = $hours % 24;

        if ($remainingHours == 0) {
            return $days . ' jour' . ($days > 1 ? 's' : '');
        }

        return $days . 'j ' . round($remainingHours) . 'h';
    }

    // Méthodes utilitaires
    public static function generateTicketNumber()
    {
        $prefix = 'TK';
        $year = date('Y');
        $lastTicket = self::whereYear('created_at', $year)
                         ->orderBy('id', 'desc')
                         ->first();
        
        $number = $lastTicket ? (int)substr($lastTicket->ticket_number, -4) + 1 : 1;
        
        return $prefix . $year . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function canBeAssigned()
    {
        return $this->status === 'pending';
    }

    public function canBeStarted()
    {
        return $this->status === 'pending' && $this->assigned_to;
    }

    public function canBeResolved()
    {
        return $this->status === 'in_progress';
    }

    public function canBeClosed()
    {
        return $this->status === 'resolved';
    }
}
