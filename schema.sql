-- -*- mode: sql; sql-product: sqlite; -*-
BEGIN;

PRAGMA foreign_keys = ON;

CREATE TABLE cursor (
	source TEXT PRIMARY KEY,
	cursor TEXT NOT NULL
);
CREATE TABLE point (
	source TEXT NOT NULL,
	ts INTEGER NOT NULL
);
CREATE TABLE point_data (
	point INTEGER NOT NULL,
	key TEXT NOT NULL,
	value,
	FOREIGN KEY(point) REFERENCES point
);
CREATE INDEX point_source on point(source, ts);
CREATE INDEX point_ts on point(ts, source);
CREATE INDEX point_key on point_data(point, key);

END;
