<?php if(!defined('KIRBY')) exit ?>

pages: false
files: false
fields:
  title:
    label: Title
    type:  text
  info:
    label: Kirby Stats
    type: info
    icon: exclamation-triangle
    text: >
      <div style="background-color: #FF8080; color: #4E4E4E;">Do not edit or delete. Statistics will be lost!<br /><br />      
      If you need to reset your statistics, deleting or renaming will be fine.<br />
      This page will be recreated when the first page hits are logged.</color>
