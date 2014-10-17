// append path to files
module.exports = function (files, prefix) {
    var results = [];

    for (i in files) {
        results[i] = prefix + files[i];
    }

    return results;
}