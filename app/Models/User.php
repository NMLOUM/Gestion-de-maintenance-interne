<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'service_id',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    // Relations
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function requestedTickets()
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function ticketComments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function ticketHistories()
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTechnicians($query)
    {
        return $query->where('role', 'technicien');
    }

    public function scopeAvailableTechnicians($query)
    {
        return $query->where('role', 'technicien')
                     ->withCount([
                         'assignedTickets as active_tickets_count' => function ($q) {
                             $q->whereIn('status', ['pending', 'in_progress']);
                         }
                     ])
                     ->orderBy('active_tickets_count', 'asc');
    }

    // Accessors
    public function getRoleDisplayAttribute()
    {
        return match($this->role) {
            'employe' => 'Employé',
            'technicien' => 'Technicien',
            'responsable_it' => 'Responsable IT',
            'direction' => 'Direction',
            default => 'Non défini'
        };
    }

    public function getIsAdminAttribute()
    {
        return in_array($this->role, ['direction']);
    }

    public function getIsTechnicianAttribute()
    {
        return in_array($this->role, ['technicien', 'responsable_it']);
    }

    public function getIsResponsableItAttribute()
    {
        return $this->role === 'responsable_it';
    }

    public function getIsDirectionAttribute()
    {
        return $this->role === 'direction';
    }

    public function getIsEmployeAttribute()
    {
        return $this->role === 'employe';
    }
}