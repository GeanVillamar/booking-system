# Sistema de Reserva de Libros

Proyecto desarrollado en Laravel para gestionar reservas de libros.

## Tecnologías

- Laravel
- PHP
- MySQL

## Estado del proyecto

En desarrollo 🚧

## Instalación

Próximamente...

## Diagrama Entidad Relacion

En el siguiente diagrama representa el esquema grafico de las entidades del sistema de reserva de libros.

```mermaid
erDiagram
    Users {
        int id PK
        string name
        string email
        string password
        timestamp created_at
        timestamp updated_at
    }

    Services {
        int id PK
        string name
        text description
        decimal price
        timestamp created_at
        timestamp updated_at
    }

    Bookings {
        int id PK
        int user_id FK
        int service_id FK
        date booking_date
        time booking_time
        string status
        timestamp created_at
        timestamp updated_at
    }

    Availabilities {
        int id PK
        int service_id FK
        date available_date
        time start_time
        time end_time
        timestamp created_at
        timestamp updated_at
    }


    Users ||--o{ Bookings : makes
    Services ||--o{ Bookings : booked
    Services ||--o{ Availabilities : has

```
