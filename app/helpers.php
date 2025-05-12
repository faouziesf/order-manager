<?php

if (!function_exists('formatStatus')) {
    function formatStatus($status) {
        $statusMap = [
            'nouvelle' => 'Nouvelle',
            'confirmée' => 'Confirmée',
            'annulée' => 'Annulée',
            'datée' => 'Datée',
            'en_route' => 'En route',
            'livrée' => 'Livrée'
        ];
        
        return $statusMap[$status] ?? $status;
    }
}

if (!function_exists('getStatusClass')) {
    function getStatusClass($status) {
        $classMap = [
            'nouvelle' => 'badge-primary',
            'confirmée' => 'badge-success',
            'annulée' => 'badge-danger',
            'datée' => 'badge-warning',
            'en_route' => 'badge-info',
            'livrée' => 'badge-secondary'
        ];
        
        return $classMap[$status] ?? 'badge-dark';
    }
}

if (!function_exists('formatPriority')) {
    function formatPriority($priority) {
        $priorityMap = [
            'normale' => 'Normale',
            'urgente' => 'Urgente',
            'vip' => 'VIP'
        ];
        
        return $priorityMap[$priority] ?? $priority;
    }
}

if (!function_exists('getPriorityClass')) {
    function getPriorityClass($priority) {
        $classMap = [
            'normale' => 'badge-secondary',
            'urgente' => 'badge-warning',
            'vip' => 'badge-danger'
        ];
        
        return $classMap[$priority] ?? 'badge-dark';
    }
}