#!/usr/bin/env node
/* -*- coding: utf-8 -*- vim: set ts=4 sw=4 expandtab */

var connect=require('connect'), app=connect();
app.use(connect.logger('dev'));
app.use(connect.directory(process.cwd(), {icons: true}));
app.use(connect.static(process.cwd(), { maxAge: 0 }));

app.listen(3001);
