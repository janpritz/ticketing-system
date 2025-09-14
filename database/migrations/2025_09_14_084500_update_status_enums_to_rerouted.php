<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Replace "In-Progress" with "Re-routed" in data and constraints.
     * Supports PostgreSQL and MySQL. For other drivers, only data updates will run.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // 1) Update existing data first (safe for all drivers)
        DB::statement("UPDATE tickets SET status = 'Re-routed' WHERE status = 'In-Progress'");
        DB::statement("UPDATE ticket_routing_histories SET status = 'Re-routed' WHERE status = 'In-Progress'");

        // 2) Adjust column constraints/types per driver
        if ($driver === 'pgsql') {
            // PostgreSQL: enum created by Schema::enum is implemented as a CHECK constraint.
            // Drop existing CHECK constraints on status, widen type if needed, then recreate CHECK with new set.

            // Drop CHECK on tickets.status
            DB::statement(<<<'SQL'
DO $$
DECLARE
    constraint_name text;
BEGIN
    SELECT con.conname INTO constraint_name
    FROM pg_constraint con
    JOIN pg_attribute att ON att.attnum = ANY(con.conkey) AND att.attrelid = con.conrelid
    JOIN pg_class rel ON rel.oid = con.conrelid
    JOIN pg_namespace nsp ON nsp.oid = rel.relnamespace
    WHERE nsp.nspname = 'public'
      AND rel.relname = 'tickets'
      AND att.attname = 'status'
      AND con.contype = 'c';
    IF constraint_name IS NOT NULL THEN
        EXECUTE 'ALTER TABLE public.tickets DROP CONSTRAINT ' || quote_ident(constraint_name);
    END IF;
END$$;
SQL);

            // Ensure type is text/varchar (no-op if already varchar)
            DB::statement("ALTER TABLE public.tickets ALTER COLUMN status TYPE VARCHAR(32)");

            // Recreate CHECK to allow new values
            DB::statement("ALTER TABLE public.tickets ADD CONSTRAINT tickets_status_check CHECK (status IN ('Open','Re-routed','Closed'))");

            // Drop CHECK on ticket_routing_histories.status
            DB::statement(<<<'SQL'
DO $$
DECLARE
    constraint_name text;
BEGIN
    SELECT con.conname INTO constraint_name
    FROM pg_constraint con
    JOIN pg_attribute att ON att.attnum = ANY(con.conkey) AND att.attrelid = con.conrelid
    JOIN pg_class rel ON rel.oid = con.conrelid
    JOIN pg_namespace nsp ON nsp.oid = rel.relnamespace
    WHERE nsp.nspname = 'public'
      AND rel.relname = 'ticket_routing_histories'
      AND att.attname = 'status'
      AND con.contype = 'c';
    IF constraint_name IS NOT NULL THEN
        EXECUTE 'ALTER TABLE public.ticket_routing_histories DROP CONSTRAINT ' || quote_ident(constraint_name);
    END IF;
END$$;
SQL);

            DB::statement("ALTER TABLE public.ticket_routing_histories ALTER COLUMN status TYPE VARCHAR(32)");
            DB::statement("ALTER TABLE public.ticket_routing_histories ADD CONSTRAINT ticket_routing_histories_status_check CHECK (status IN ('Open','Re-routed','Closed'))");
        } elseif ($driver === 'mysql') {
            // MySQL: change ENUM set
            DB::statement("ALTER TABLE `tickets` MODIFY COLUMN `status` ENUM('Open','Re-routed','Closed') NOT NULL DEFAULT 'Open'");
            DB::statement("ALTER TABLE `ticket_routing_histories` MODIFY COLUMN `status` ENUM('Open','Re-routed','Closed') NOT NULL");
        } else {
            // Other drivers (sqlite, sqlsrv) - best effort: no schema change, data already updated.
            // If needed, custom handling can be added here per driver.
        }
    }

    /**
     * Reverse the migrations.
     *
     * Revert to "In-Progress" and original constraints.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // Drop current CHECK and recreate with 'In-Progress'
            DB::statement(<<<'SQL'
DO $$
DECLARE
    constraint_name text;
BEGIN
    SELECT con.conname INTO constraint_name
    FROM pg_constraint con
    JOIN pg_attribute att ON att.attnum = ANY(con.conkey) AND att.attrelid = con.conrelid
    JOIN pg_class rel ON rel.oid = con.conrelid
    JOIN pg_namespace nsp ON nsp.oid = rel.relnamespace
    WHERE nsp.nspname = 'public'
      AND rel.relname = 'tickets'
      AND att.attname = 'status'
      AND con.contype = 'c';
    IF constraint_name IS NOT NULL THEN
        EXECUTE 'ALTER TABLE public.tickets DROP CONSTRAINT ' || quote_ident(constraint_name);
    END IF;
END$$;
SQL);
            DB::statement("ALTER TABLE public.tickets ADD CONSTRAINT tickets_status_check CHECK (status IN ('Open','In-Progress','Closed'))");

            DB::statement(<<<'SQL'
DO $$
DECLARE
    constraint_name text;
BEGIN
    SELECT con.conname INTO constraint_name
    FROM pg_constraint con
    JOIN pg_attribute att ON att.attnum = ANY(con.conkey) AND att.attrelid = con.conrelid
    JOIN pg_class rel ON rel.oid = con.conrelid
    JOIN pg_namespace nsp ON nsp.oid = rel.relnamespace
    WHERE nsp.nspname = 'public'
      AND rel.relname = 'ticket_routing_histories'
      AND att.attname = 'status'
      AND con.contype = 'c';
    IF constraint_name IS NOT NULL THEN
        EXECUTE 'ALTER TABLE public.ticket_routing_histories DROP CONSTRAINT ' || quote_ident(constraint_name);
    END IF;
END$$;
SQL);
            DB::statement("ALTER TABLE public.ticket_routing_histories ADD CONSTRAINT ticket_routing_histories_status_check CHECK (status IN ('Open','In-Progress','Closed'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE `tickets` MODIFY COLUMN `status` ENUM('Open','In-Progress','Closed') NOT NULL DEFAULT 'Open'");
            DB::statement("ALTER TABLE `ticket_routing_histories` MODIFY COLUMN `status` ENUM('Open','In-Progress','Closed') NOT NULL");
        }

        // Revert data
        DB::statement("UPDATE tickets SET status = 'In-Progress' WHERE status = 'Re-routed'");
        DB::statement("UPDATE ticket_routing_histories SET status = 'In-Progress' WHERE status = 'Re-routed'");
    }
};