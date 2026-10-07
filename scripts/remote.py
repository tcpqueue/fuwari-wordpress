import argparse
import json
import pathlib
import sys

import paramiko

parser = argparse.ArgumentParser()
parser.add_argument('--script')
parser.add_argument('--put', nargs=2, action='append', default=[])
parser.add_argument('--get', nargs=2, action='append', default=[])
args = parser.parse_args()
base = pathlib.Path(__file__).resolve().parent.parent
credentials = json.loads((base / 'work/connection.json').read_text())
client = paramiko.SSHClient()
client.load_host_keys(str(base / 'work/known_hosts'))
client.set_missing_host_key_policy(paramiko.RejectPolicy())
client.connect(credentials['host'], port=22, username='root', password=credentials['password'], look_for_keys=False, allow_agent=False, timeout=15)
try:
    if args.put or args.get:
        with client.open_sftp() as sftp:
            for local, remote in args.put:
                sftp.put(local, remote)
            for remote, local in args.get:
                sftp.get(remote, local)
    if args.script:
        script = pathlib.Path(args.script).read_text()
        stdin, stdout, stderr = client.exec_command('bash -se', get_pty=False)
        stdin.write(script)
        stdin.channel.shutdown_write()
        for line in stdout:
            sys.stdout.write(line)
            sys.stdout.flush()
        errors = stderr.read().decode()
        if errors:
            sys.stderr.write(errors)
        sys.exit(stdout.channel.recv_exit_status())
finally:
    client.close()
