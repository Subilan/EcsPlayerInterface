<template>
  <div class="ecs-player-interface">
    <router-view/>
  </div>
</template>

<style lang="less" scoped>
.ecs-player-interface {
  position: absolute;
  left: 50%;
  top: 50%;
  -webkit-transform: translate(-50%, -50%);
  transform: translate(-50%, -50%);
}
</style>