#!/bin/bash
cd ../public
gnome-terminal -- bash -c "symfony server:start; exec bash"
sleep 2
firefox http://127.0.0.1:8000