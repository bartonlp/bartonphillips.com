from __future__ import print_function
import time
import intrinio_sdk as intrinio
from intrinio_sdk.rest import ApiException

intrinio.ApiClient().set_api_key('OjIwM2ZlMDU3NmFlOWQyZDA5ZTVjYjM2Y2FlZWYzZDQz')
intrinio.ApiClient().allow_retries(True)

identifier = "T"
source = "bats_delayed"

response = intrinio.SecurityApi().get_security_realtime_price(identifier, source=source)
print(response)
