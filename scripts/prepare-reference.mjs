import fs from 'node:fs';
import path from 'node:path';
import {execFileSync} from 'node:child_process';
const commit='6d39b0dec41282e7852e23e032998a5789abee28';
const ref=path.resolve(process.env.FUWARI_REFERENCE||'.cache/fuwari-reference');
if(process.platform==='linux'&&process.cwd().startsWith('/mnt/'))throw new Error('Build from a Linux-native directory.');
if(!fs.existsSync(ref)){
 fs.mkdirSync(path.dirname(ref),{recursive:true});
 execFileSync('git',['clone','--filter=blob:none','https://github.com/saicaca/fuwari.git',ref],{stdio:'inherit'});
 execFileSync('git',['checkout','--detach',commit],{cwd:ref,stdio:'inherit'});
}
if(execFileSync('git',['rev-parse','HEAD'],{cwd:ref,encoding:'utf8'}).trim()!==commit)throw new Error('Reference commit does not match the pinned version.');
const config=path.join(ref,'src/config.ts');
fs.writeFileSync(config,fs.readFileSync(config,'utf8').replace(/(banner:\s*\{\s*enable:)\s*false/,'$1 true'));
execFileSync('pnpm',['install','--frozen-lockfile'],{cwd:ref,stdio:'inherit'});
execFileSync('pnpm',['build'],{cwd:ref,stdio:'inherit'});
