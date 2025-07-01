<?php
$readonly_password = 'p@$$word';
$operator_password = 'P@$$word';

echo "Read Only user (user1) hash:\n";
echo password_hash($readonly_password, PASSWORD_DEFAULT) . "\n\n";

echo "Operator user (ops1) hash:\n";
echo password_hash($operator_password, PASSWORD_DEFAULT) . "\n"; 
