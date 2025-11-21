<?php
function custom_hash($password) {
    // Simple, consistent hashing that will always produce the same output
    $pepper = "K!ckst3r_2024_F!x3d_P3pp3r_@123";
    
    // Use multiple rounds of reliable hashing
    $hash = $password . $pepper;
    $hash = hash('sha256', $hash);
    $hash = hash('sha256', $hash . $pepper);
    
    return $hash;
}
?>