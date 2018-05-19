// Block chain

/*
const hash = require('object-hash');

const items = [
               {name: "Barton Phillips", age: 74},
               {name: "Ingrid Phillips", age: 80 },
               {name: "Michael Phillips", age: 48}
];

let chain = [];
let back = 0;

for(let item of items) {
  let h = hash(item);
  console.log(h);
  chain.push({link: h, item, backlink: back});
  back = h;
}

console.log(chain);
*/

const Blockchain = require('ethereumjs-blockchain');
const Block = require('ethereumjs-block');
const ethUtil = require('ethereumjs-util');

var blocks = [];
var genesisBlock;
const blockchain = new Blockchain();
blockchain.validate = false;
genesisBlock = new Block();
genesisBlock.setGenesisParams();
blockchain.putGenesis(genesisBlock, function (err) {
  if (err) return done(err);
  blocks.push(genesisBlock);
  addNextBlock(1);
});

//const item = new Block({name: "Barton Phillips", age: 74});

function addNextBlock (blockNumber) {
  var block = new Block();
  block.header.number = ethUtil.toBuffer(blockNumber);
  block.header.difficulty = '0xfffffff';
  block.header.parentHash = blocks[blockNumber - 1].hash();
  block.transactions = [{name: "Barton Phillips", age: 74}];
  console.log("block:", block.header.number, block.header.parentHash, block.transactions, block);
  blockchain.putBlock(block, function (err) {
    if (err) return done(err)
    console.log("add", blockNumber);
    blocks.push(block);

    if(blocks.length === 10) {
      console.log('added 10 blocks');
    } else {
      addNextBlock(blockNumber + 1);
    }
  });
};



