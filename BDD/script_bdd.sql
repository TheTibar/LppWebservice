CREATE TABLE IF NOT EXISTS lpp_log LIKE rpps_log;
alter table lpp_log drop COLUMN region_id;

CREATE TABLE IF NOT EXISTS lpp_process LIKE rpps_process;
alter table lpp_process drop COLUMN region_id;

CREATE TABLE IF NOT EXISTS lpp_param LIKE rpps_param;

delete from lpp_param;

insert into lpp_param (param_name, param_value)
values ('tmp_lpp_path', '/../LPPFiles/');

CREATE TABLE IF NOT EXISTS lpp_file_history LIKE rpps_file_history;

drop table lpp_current_data;
create table lpp_current_data
(
    code_lpp int,
    label nvarchar(200),
    prix float,
    debut_validite nvarchar(8),
    maj_971 float,
    maj_972 float,
    maj_973 float,
    maj_974 float,
    process_id int
);