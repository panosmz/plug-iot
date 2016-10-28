function startup()
    print('---in startup')
    dofile('main.lc')
    end

tmr.alarm(0,5000,0,startup)