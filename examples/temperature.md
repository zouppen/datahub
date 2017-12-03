# Temperature measurement

```sql
SELECT ts, d1.value AS temp, d2.value AS humidity
FROM point p
JOIN point_data d1 ON (d1.point = p.rowid AND d1.key='temperature_C')
JOIN point_data d2 ON (d2.point = p.rowid AND d2.key='humidity')
JOIN point_data d3 ON (d3.point = p.rowid AND d3.key='id')
WHERE source='hacklabjkl' AND d3.value=252;
```

To dump all data points as CSV for plotting:

	sqlite3 -header -csv db/db.sqlite "select datetime(ts,'unixepoch','localtime') as time,d1.value as temperature,d2.value as humidity from point p join point_data d1 on (d1.point = p.rowid and d1.key='temperature_C') join point_data d2 on (d2.point = p.rowid and d2.key='humidity') join point_data d3 on (d3.point = p.rowid and d3.key='id') where source='hacklabjkl' and d3.value=252;"
