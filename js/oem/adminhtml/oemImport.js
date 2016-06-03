/**
 * Number.prototype.format(n, x, s, c)
 * 
 * @param integer n: length of decimal
 * @param integer x: length of whole part
 * @param mixed   s: sections delimiter
 * @param mixed   c: decimal delimiter
 */
Number.prototype.format = function(n, x, s, c) {
	var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
		num = this.toFixed(Math.max(0, ~~n));

	return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};

//Object.prototype.mergeObject = function(withWhat) {
mergeObject = function(subj, withWhat) {
	var _merge = function(src, dest) {
		for (var prop in src) {
			if (dest.hasOwnProperty(prop)) {
				if ((dest[prop] !== null) && (typeof(dest[prop]) === 'object')) {
					_merge(src[prop], dest[prop]);
				} else {
					dest[prop] = src[prop];
				}
			}
		}
		return dest;
	};
	return _merge(withWhat, subj);
//	return _merge(withWhat, this);
};