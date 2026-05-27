const mammoth = require("mammoth");
const file = process.argv[2];

mammoth.convertToHtml({ path: file })
    .then(result => {
        console.log(result.value);
    })
    .catch(err => {
        console.error(err);
        process.exit(1);
    });