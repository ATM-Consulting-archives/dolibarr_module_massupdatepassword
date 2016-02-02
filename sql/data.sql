DELETE FROM llx_extrafields WHERE rowid=1039851;
INSERT INTO llx_extrafields (rowid, name, entity, elementtype, tms, label, type, size, fieldunique, fieldrequired, pos, alwayseditable, param) VALUES
(1039851, 'pwd_expiration_dt', 1, 'user', NOW(), "Date d'expiration du mot de passe", 'date', '', 0, 0, 0, 0, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
ALTER TABLE llx_user_extrafields ADD pwd_expiration_dt datetime;