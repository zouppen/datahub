-- -*- mode: sql; sql-product: sqlite; -*-
BEGIN;

CREATE TABLE cursor (cursor text);
CREATE TABLE temp (ts integer not null, value real not null);
CREATE INDEX temp_ts on temp(ts);

END;
