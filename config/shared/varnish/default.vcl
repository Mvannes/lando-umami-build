# This is the configuration of varnish this file contains several checks that decides to cache a page or not.
# Original author for Varnish 3: Daan Molenaar
# This varnish configuration has been upgraded for Varnish 4.

sub vcl_recv {
  #  unset req.http.X-Forwarded-For;
  #  set req.http.X-Forwarded-For = client.ip;

  if (!req.http.X-Forwarded-For) {
    set req.http.X-Forwarded-For = client.ip;
  }

  ## check the custom configuration file if the url is allowed to be handled by varnish

  ## Default request checks
  if (req.method != "GET" &&
  req.method != "HEAD" &&
  req.method != "PUT" &&
  req.method != "POST" &&
  req.method != "TRACE" &&
  req.method != "OPTIONS" &&
  req.method != "DELETE") {
    # Non-RFC2616 or CONNECT which is weird.
    return (pipe);
  }
  if (req.method != "GET" && req.method != "HEAD") {
    # We only deal with GET and HEAD by default
    return (pass);
  }

  ## Remove has_js and Google Analytics cookies.
  set req.http.Cookie = regsuball(req.http.Cookie, "(^|;\s*)(__[a-z]+)=[^;]*", "");

  ## Remove a ";" prefix, if present.
  set req.http.Cookie = regsub(req.http.Cookie, "^;\s*", "");
  ## Remove empty cookies.
  if (req.http.Cookie ~ "^\s*$") {
    unset req.http.Cookie;
  }
  ## Let's have a little grace
  # TODO: grace not supported in varnish 4.1 set req.grace = 60s;

  ## Normalize the Accept-Encoding header
  ## as per: http://varnish-cache.org/wiki/FAQ/Compression
  if (req.http.Accept-Encoding) {
    if (req.url ~ "\.(jpg|png|gif|gz|tgz|bz2|tbz|mp3|ogg)$" || req.url ~ "robots\.txt") {
      # No point in compressing these
      unset req.http.Accept-Encoding;
    } elsif (req.http.Accept-Encoding ~ "gzip") {
      set req.http.Accept-Encoding = "gzip";
    } elsif (req.http.Accept-Encoding ~ "deflate") {
      set req.http.Accept-Encoding = "deflate";
    } else {
      # unkown encoding algorithm
      unset req.http.Accept-Encoding;
    }
  }

  ## No varnish for install,update.php or cron.php
  if (req.url ~ "install\.php|update\.php|cron\.php") {
    return (pass);
  }

  if (req.http.Cookie ~ "NO_CACHE=Y") {
    # there is a no "no-cache" cookie set in the site
    return (pass);
  }

  set req.http.X-SYNETIC-COOKIE-NO-SESS = regsuball(req.http.Cookie, "(^|; ) *SESS[A-Za-z0-9=]+;? *", "\1");

  # Check for JanusAB test cookies, if they exist, remove the unique id one.
  if (req.http.Cookie ~ "([!-~]*)(_)([!-~]*)(_)([!-~]*)(=)([!-~]*)" &&
    req.http.Cookie ~ "([!-~]*)(_)([!-~]*)(_)([!-~]*)(_)(ID)(=)([!-~]*)") {
    set req.http.X-JANUS-EXPERIMENT-COOKIE = regsuball(
      req.http.Cookie,
      "([!-~]*)(_)([!-~]*)(_)([!-~]*)(_)(ID)(=)([!-~]*)",
      ""
    );
  } else {
    set req.http.X-JANUS-EXPERIMENT-COOKIE = "-1";
  }
  return(hash);
}

sub vcl_backend_response {

}

### vcl_hash creates the key for varnish under which the object is stored. It is
### possible to store the same url under 2 different keys, by making vcl_hash
### create a different hash.
sub vcl_hash {
  ## these 2 entries are the default ones used for vcl. Below we add our own.
  hash_data(req.url);
  hash_data(req.http.host);
  hash_data(req.http.Front-End-Https);

  hash_data(req.http.X-JANUS-EXPERIMENT-COOKIE);
  unset req.http.X-JANUS-EXPERIMENT-COOKIE;

  ## add the cookie to the hash
  # set req.hash += req.http.X-SYNETIC-COOKIE-NO-SESS;

  return(lookup);
}

sub vcl_backend_error {
  if (beresp.status >= 500 && beresp.status <= 505) {
    set beresp.http.Content-Type = "text/html; charset=utf-8";
    synthetic ("<html><head><title>Error happened</title></head><body><h1>Error description</h1></body></html>");
  }
  return (deliver);
}

sub vcl_deliver {
  unset resp.http.Via;
  if (obj.hits > 0) {
    set resp.http.X-Varnish-Cache = "HIT";
  } else {
    set resp.http.X-Cache = "MISS";
  }

  return(deliver);
}
